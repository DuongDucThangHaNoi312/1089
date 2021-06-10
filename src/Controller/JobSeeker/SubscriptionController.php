<?php

namespace App\Controller\JobSeeker;

use App\Entity\Stripe\Customer;
use App\Entity\Stripe\StripeInvoice;
use App\Entity\SubscriptionPlan\JobSeekerSubscriptionPlan;
use App\Entity\User;
use App\Entity\User\JobSeekerUser;
use App\Form\JobSeeker\Subscription\PayInactiveSubscriptionType;
use App\Form\JobSeeker\Subscription\StripePaymentType;
use App\Form\JobSeeker\Subscription\UpdatePaymentMethodType;
use App\Repository\Stripe\StripeInvoiceRepository;
use App\Repository\SubscriptionPlan\JobSeekerSubscriptionPlanRepository;
use App\Service\StripeSubscriptionProcessor;
use App\Service\SubscriptionManager;
use App\Service\SubscriptionProcessorInterface;
use Exception;
use Knp\Component\Pager\PaginatorInterface;
use Stripe\Checkout\Session;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

class SubscriptionController extends AbstractController
{
    /**
     * @Route("/job-seeker/subscription/{id}/cancel", name="job_seeker_subscription_cancel")
     * @param JobSeekerUser\Subscription $subscription
     * @param Request $request
     * @param SubscriptionManager $subscriptionManager
     * @return RedirectResponse
     * @throws Exception
     */
    public function cancelSubscription(JobSeekerUser\Subscription $subscription, Request $request, SubscriptionManager $subscriptionManager, SubscriptionProcessorInterface $subscriptionProcessor)
    {
        $this->denyAccessUnlessGranted('edit_subscription', $this->getUser());

        if ($subscription->getPaymentProcessorSubscriptionId() != '') {
            if ($subscriptionProcessor->cancelSubscription($subscription->getPaymentProcessorSubscriptionId())) {
                $subscriptionManager->setFutureCancellation($subscription);
                $this->addFlash('success', 'Your subscription has been cancelled.');
            } else {
                $this->addFlash('error', 'Error cancelling your subscription please try again.');
            }
        } else {
            // Free Subscriptions are not stored in Stripe but subscription logic still applies.
            if ($subscription->getSubscriptionPlan()->getPrice() <= 0.0) {
                $subscriptionManager->setFutureCancellation($subscription);
                $this->addFlash('success', 'Your subscription has been cancelled.');
            } else {
                $this->addFlash('error', 'Error cancelling your subscription please try again.');
            }
        }

        return $this->redirect($request->headers->get('referer'));

    }

    /**
     * @Route("/job-seeker/subscription", name="job_seeker_subscription")
     * @return Response
     */
    public function subscription(Request $request, SubscriptionProcessorInterface $subscriptionProcessor, RouterInterface $router, SessionInterface $session)
    {
        // CIT-807: Redirecting to registration flow. If they have not completed it.
        if ($this->isGranted('ROLE_PENDING_JOBSEEKER')) {
            $this->addFlash('warning', 'Please complete your Registration before accessing your subscription');
            /** @var JobSeekerUser $user */
            $user = $this->getUser();
            $url = $this->generateUrl('job_seeker_registration_step_two');
            if ($user->getConfirmationToken()) {
                $url = $this->generateUrl('job_seeker_registration_step_one_verify');
            } elseif ($user->getCity() && $user->getState()) {
                $url = $this->generateUrl('job_seeker_registration_step_three');
            }
            return $this->redirect($url);
        }

        /** @var JobSeekerUser $user */
        $user = $this->getUser();

        $this->denyAccessUnlessGranted('edit_subscription', $user);

        /** @var JobSeekerUser $user */
        $user = $this->getUser();
        $card = null;
        $customerId = $user->getCustomerId();
        if ($customerId) {
            $card = $subscriptionProcessor->retrieveCustomerPaymentMethod($customerId);
        }

        $updatePaymentForm = $this->createForm(UpdatePaymentMethodType::class, null, [
            'action' => $this->generateUrl('job_seeker_update_payment_method'),
        ]);

        $data = null;
        if ($user->getSubscription() && ($user->getSubscription()->getStripeSubscription() or $user->getSubscription()->getSubscriptionPlan()->getPrice() <= 0.0)) {
            $data = ['subscriptionPlans' => $user->getSubscription()->getSubscriptionPlan()];
        }

        $choosePlanForm = $this->createForm(StripePaymentType::class, $data, [
            'user' => $user,
            'action' => $this->generateUrl('job_seeker_choose_subscription_plan'),
        ]);

        $inactiveSubscriptionForm = null;
        if ($card != null && $user->getSubscription() && $user->getSubscription()->getIsPaid() == false) {
            $inactiveSubscriptionForm = $this->createForm(PayInactiveSubscriptionType::class, ['subscriptionId' => $user->getSubscriptionId()], [
            'action' => $this->generateUrl('job_seeker_subscription_plan_pay'),
            ]);
        }

        $type = 'choose';
        if ($user->getSubscription() && !$user->getSubscription()->getSubscriptionPlan()->getIsTrial() && $user->getSubscription()->getPaymentProcessorSubscriptionId() != '') {
            $type = 'change';
        }

        $update = $request->get('update');
        if ($user->getSubscription() && $user->getSubscription()->getIsPaid() == false && $user->getSubscription()->getCancelledAt() == null && $update == null) {
            $update = 'payment';
        } elseif ($user->getSubscription() && $user->getSubscription()->isCancelled() && $update == null) {
            $update = 'subscription';
        }

        return $this->render('job_seeker/subscription/subscription.html.twig', [
            'updatePaymentForm' => $updatePaymentForm->createView(),
            'choosePlanForm' => $choosePlanForm->createView(),
            'inactiveSubscriptionForm' => $inactiveSubscriptionForm ? $inactiveSubscriptionForm->createView(): null,
            'card' => $card,
            'update' => $update,
            'type' => $type,
            'user' => $user,
            'subscription' => $user->getSubscription()
        ]);
    }


    /**
     * @Route("/job-seeker/subscription-plan/pay", name="job_seeker_subscription_plan_pay")
     * @param Request $request
     * @param RouterInterface $router
     * @param SessionInterface $session
     * @param SubscriptionProcessorInterface $subscriptionProcessor
     * @param SubscriptionManager $subscriptionManager
     * @return RedirectResponse
     */
    public function payInactiveSubscription(Request $request, RouterInterface $router, SessionInterface $session,  SubscriptionProcessorInterface $subscriptionProcessor, SubscriptionManager $subscriptionManager) {
        /** @var JobSeekerUser $user */
        $user = $this->getUser();

        $form = $this->createForm(PayInactiveSubscriptionType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $token = $request->get('stripeToken');

            if ($token) {
                $subscriptionProcessor->setFlashBag($session->getFlashBag());
                $isUpdated = $subscriptionProcessor->updateCustomerPaymentMethod($user, $token);
                if ($isUpdated) {
                    $data = $form->getData();
                    $paySubscription = $subscriptionProcessor->paySubscription($data['subscriptionId']);
                    // If successful
                    if ($paySubscription) {
                        // Update the Users subscription to Payed
                        $subscriptionManager->setIsPaid($user->getSubscription());
                        $em = $this->getDoctrine()->getManager();
                        $user->setRawStripeCustomer($isUpdated);
                        $em->persist($user);
                        $em->flush();
                        $session->set('subscriptionNotPaid', false);
                        $this->addFlash('success', 'Successfully payed for subscription');
                    } else {
                        $this->addFlash('error', 'Unable to complete payment, please try again.');
                    }

                    return $this->redirectToRoute('job_seeker_subscription');
                }
            } else {
                $this->addFlash('error', 'Unable to complete payment, please try again.');
            }
        }

        return $this->redirect($request->headers->get('referer'));




    }

    /**
     * @Route("/job-seeker/subscription-plan/choose", name="job_seeker_choose_subscription_plan")
     * @param RouterInterface $router
     * @param SessionInterface $session
     * @param Request $request
     * @param SubscriptionManager $subscriptionManager
     * @return Response
     * @throws Exception
     */
    public function choosePlan(RouterInterface $router, SessionInterface $session, Request $request, SubscriptionManager $subscriptionManager) {
        // cannot use denyAccessUnlessGranted here, because would be incompatible with implementation of SubscriptionStatusVoter
        // in the context of selecting / reactivating your Job Seeker's subscription

        /** @var JobSeekerUser $user */
        $user = $this->getUser();
        if (false == $this->getUser() instanceof JobSeekerUser) {
            return new RedirectResponse($router->generate('access-denied'));
        }

        $data = null;
        if ($user->getSubscription() && ($user->getSubscription()->getPaymentProcessorSubscriptionId() != '' or $user->getSubscription()->getSubscriptionPlan()->getPrice() <= 0.0)) {
            $data = ['subscriptionPlans' => $user->getSubscription()->getSubscriptionPlan()];
        }
        $form = $this->createForm(StripePaymentType::class, $data, [
            'user' => $user
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $token = $request->get('stripeToken');
            $formData = $form->getData();
            /** @var JobSeekerSubscriptionPlan $jobSeekerSubscriptionPlan */
            $jobSeekerSubscriptionPlan = $formData['subscriptionPlans'];

            if (!$jobSeekerSubscriptionPlan->getIsActive()) {
                $this->addFlash('error', 'This subscription is no longer active. Please choose an active subscription and try again.');
                return $this->redirectToRoute('job_seeker_subscription');
            }

            // If it's an upgrade, process Subscription Right now
            if ($subscriptionManager->isUpgrade($user->getSubscription(), $jobSeekerSubscriptionPlan)) {
                $isSubscribed = false;
                $isPaid = false;
                $expiresOn = null;
                $statusArray = $subscriptionManager->processJobSeekerSubscription($user, $jobSeekerSubscriptionPlan, $session->getFlashBag(), $token);
                extract($statusArray);

                if ($isSubscribed) {
                    $subscriptionManager->subscribeJobSeeker($user, $jobSeekerSubscriptionPlan, $isPaid, false, $expiresOn);
                    $session->set('subscriptionExpired', false);


                    if ($isPaid) {
                        $this->addFlash('success', 'You have subscribed to ' . $jobSeekerSubscriptionPlan->getName());
                        $session->set('subscriptionNotPaid', false);
                    } else {
                        $this->addFlash('error', 'There was an issue with your payment, please try again.');
                        return $this->redirectToRoute('job_seeker_subscription', ['update' => 'payment']);
                    }

                    return $this->redirectToRoute('job_seeker_subscription');
                }
                else {
                    $this->addFlash('error', 'Unable to subscribe to ' . $jobSeekerSubscriptionPlan->getName());
                }
            } else {
                // Queue Subscription Change
                $subscriptionManager->queueSubscriptionChange($user->getSubscription(), $jobSeekerSubscriptionPlan);
                $this->addFlash('success', 'You have queued a subscription change');
                return $this->redirectToRoute('job_seeker_subscription');
            }

        }

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     *
     * @Route("/job-seeker/subscription/{id}/reactivate", name="job_seeker_reactivate")
     *
     * @param JobSeekerSubscriptionPlan $jobSeekerSubscriptionPlan
     * @param SessionInterface $session
     * @param StripeSubscriptionProcessor $subscriptionProcessor
     * @param SubscriptionManager $subscriptionManager
     * @return Response
     * @throws Exception
     */
    public function reactivate(JobSeekerSubscriptionPlan $jobSeekerSubscriptionPlan, SessionInterface $session, Request $request, SubscriptionProcessorInterface $subscriptionProcessor, SubscriptionManager $subscriptionManager)
    {
        $this->denyAccessUnlessGranted('activate', $jobSeekerSubscriptionPlan);

        /** @var JobSeekerUser $user */
        $user = $this->getUser();
        $isPaid = false;

        if (!$jobSeekerSubscriptionPlan->getIsActive()) {
            $this->addFlash('error', 'This subscription is no longer active. Please choose an active subscription and try again.');
            return $this->redirectToRoute('job_seeker_subscription');
        }
        $expiresOn = null;
        if ($user->getSubscriptionId() != '') {
            $subscriptionProcessor->setFlashBag($session->getFlashBag());
            $subscription = $subscriptionProcessor->reactivateSubscription($user, $user->getSubscriptionId());
            if ($subscription) {
                $isPaid = $subscription->status == 'active' || $subscription->status == 'canceled' ? true : false;
                $em = $this->getDoctrine()->getManager();
                $timestamp = $subscription->current_period_end;
                $expiresOn = new \DateTime('@'. $timestamp);
                $user->setRawStripeCustomer($subscription->customer);
                $user->getSubscription()->setRawStripeSubscription($subscription->id);
                $user->getSubscription()->setExpiresAt($expiresOn);
                $em->persist($user->getSubscription());
                $em->persist($user);
                $em->flush();
            }
        } else if ($jobSeekerSubscriptionPlan->getPrice() <= 0.0) {
            // Free Subscriptions shouldn't be stored in Stripe so it bypasses the StripeSubscriptionProcessor::reactivate method and goes straight to the subscription.
            $isPaid = true;
        }

        if ($isPaid) {
            $subscriptionManager->subscribeJobSeeker($user, $jobSeekerSubscriptionPlan, $isPaid, false, $expiresOn);
            $session->set('subscriptionExpired', false);
            $session->set('subscriptionNotPaid', false);
            $this->addFlash('success', 'You have reactivated subscription to ' . $jobSeekerSubscriptionPlan->getName());
            return $this->redirectToRoute('job_seeker_subscription');

        } else {
            $this->addFlash('error', 'Unable to reactivate subscription to ' . $jobSeekerSubscriptionPlan->getName());
            return $this->redirect($request->headers->get('referer'));
        }
    }

    /**
     * @Route("/job-seeker/payment-method/update", name="job_seeker_update_payment_method")
     * @param Request $request
     * @param RouterInterface $router
     * @param SessionInterface $session
     * @param StripeSubscriptionProcessor $subscriptionProcessor
     * @param SubscriptionManager $subscriptionManager
     * @return RedirectResponse|Response
     */
    public function updatePaymentMethod(Request $request, RouterInterface $router, SessionInterface $session,  SubscriptionProcessorInterface $subscriptionProcessor, SubscriptionManager $subscriptionManager) {
        /** @var JobSeekerUser $user */
        $user = $this->getUser();
        if (false == $this->getUser() instanceof JobSeekerUser) {
            return new RedirectResponse($router->generate('access-denied'));
        }

        $form = $this->createForm(UpdatePaymentMethodType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $token = $request->get('stripeToken');
            if ($token) {
                $subscriptionProcessor->setFlashBag($session->getFlashBag());
                $isUpdated = $subscriptionProcessor->updateCustomerPaymentMethod($user, $token);
                if ($isUpdated) {
                    $em = $this->getDoctrine()->getManager();
                    $user->setRawStripeCustomer($isUpdated);
                    $em->persist($user);
                    $em->flush();
                    return $this->redirect($request->headers->get('referer'));
                }
            } else {
                $this->addFlash('error', 'Invalid Payment Method, please try again.');
            }
        }

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/job-seeker/transaction-history", name="job_seeker_transaction_history")
     * @param Request $request
     * @param SubscriptionProcessorInterface $subscriptionProcessor
     *
     * @return Response
     */
    public function displayTransactionHistory(Request $request, SubscriptionProcessorInterface $subscriptionProcessor)
    {
        /** @var JobSeekerUser $user */
        $user = $this->getUser();

        $subscriptionProcessor->setFlashBag($request->getSession()->getFlashBag());
        $invoices = $subscriptionProcessor->retrieveInvoices($user->getCustomerId());

        return $this->render('invoice/subscription_transaction_history.html.twig', [
           'invoices' => $invoices,
           'city' => $user->getCity(),
        ]);

    }
}
