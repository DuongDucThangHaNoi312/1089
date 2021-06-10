<?php

namespace App\Controller\City;

use App\Entity\City;
use App\Entity\City\Subscription;
use App\Entity\SubscriptionPlan;
use App\Entity\SubscriptionPlan\CitySubscriptionPlan;
use App\Entity\User\CityUser;
use App\Entity\User\JobSeekerUser;
use App\Entity\User\SubscriptionChangeRequest;
use App\Form\City\Subscription\StripePaymentType;
use App\Form\JobSeeker\Subscription\PayInactiveSubscriptionType;
use App\Form\JobSeeker\Subscription\UpdatePaymentMethodType;
use App\Service\StripeSubscriptionProcessor;
use App\Service\SubscriptionProcessorInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Service\SubscriptionManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\RouterInterface;


class SubscriptionController extends AbstractController
{
    /**
     * @Route("/city/{slug}/subscription/{id}/cancel", name="city_subscription_cancel")
     * @param Subscription $subscription
     * @param Request $request
     * @param SubscriptionManager $subscriptionManager
     * @return RedirectResponse
     * @throws \Exception
     */
    public function cancelSubscription(Subscription $subscription, Request $request, SubscriptionManager $subscriptionManager, SubscriptionProcessorInterface $subscriptionProcessor)
    {
        $this->denyAccessUnlessGranted('edit_subscription', $this->getUser());

        if ($subscription->getPaymentProcessorSubscriptionId() != '') {
            if ($subscriptionProcessor->cancelSubscription($subscription->getPaymentProcessorSubscriptionId())) {
                $subscriptionManager->setFutureCancellation($subscription, true);
                $this->addFlash('success', 'Your subscription has been cancelled.');
            } else {
                $this->addFlash('error', 'Error cancelling your subscription please try again.');
            }
        } else {
            // Free Subscriptions are not stored in Stripe but subscription logic still applies.
            if ($subscription->getSubscriptionPlan()->getPriceByFTE($subscription->getCity()->getCountFTE()) <= 0.0) {
                $subscriptionManager->setFutureCancellation($subscription, true);
                $this->addFlash('success', 'Your subscription has been cancelled.');
            } else {
                $this->addFlash('error', 'Error cancelling your subscription please try again.');
            }
        }

        return $this->redirect($request->headers->get('referer'));

    }

    /**
     * @Route("/city/{slug}/subscription", name="city_subscription")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     * @param City $city
     * @param RouterInterface $router
     * @param SubscriptionProcessorInterface $subscriptionProcessor
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function subscription(Request $request, City $city, RouterInterface $router, SubscriptionProcessorInterface $subscriptionProcessor, SessionInterface $session)
    {
        $this->denyAccessUnlessGranted('edit_subscription',$this->getUser());

        if ($this->isGranted('ROLE_CITYADMIN')) {
            /** @var CityUser $user */
            $user = $this->getUser();
            $card = null;
            $customerId = $user->getCustomerId();
            if ($customerId) {
                $card = $subscriptionProcessor->retrieveCustomerPaymentMethod($customerId);
            }

            $updatePaymentForm = $this->createForm(UpdatePaymentMethodType::class, null, [
                'action' => $this->generateUrl('city_update_payment_method', ['slug' => $city->getSlug()]),
            ]);

            $data = null;
            if ($city->getSubscription() && ($city->getSubscription()->getPaymentProcessorSubscriptionId() != '' or $city->getSubscription()->getSubscriptionPlan()->getPriceByFTE($city->getCountFTE()) <= 0.0)) {
                $data = ['subscriptionPlans' => $city->getSubscription()->getSubscriptionPlan()];
            }

            $choosePlanForm = $this->createForm(StripePaymentType::class, $data, [
                'city' => $city,
                'action' => $this->generateUrl('city_choose_subscription_plan', ['slug' => $city->getSlug()])
            ]);

            $inactiveSubscriptionForm = null;
            if ($card != null && $city->getSubscription() && $city->getSubscription()->getIsPaid() == false) {
                $inactiveSubscriptionForm = $this->createForm(PayInactiveSubscriptionType::class, ['subscriptionId' => $city->getSubscriptionId()], [
                    'action' => $this->generateUrl('city_subscription_plan_pay', ['slug' => $city->getSlug()]),
                ]);
            }

            $type = 'choose';
            if ($city->getSubscription() && !$city->getSubscription()->getSubscriptionPlan()->getIsTrial() && $city->getSubscription()->getPaymentProcessorSubscriptionId() != '') {
                $type = 'change';
            }

            $update = $request->get('update');
            if ($city->getSubscription() && $city->getSubscription()->getIsPaid() == false && $city->getSubscription()->getCancelledAt() == null && $update == null) {
                $update = 'payment';
            } elseif ($city->getSubscription() && $city->getSubscription()->isCancelled() && $update == null) {
                $update = 'subscription';
            }

            return $this->render('city/subscription/subscription.html.twig', [
                'updatePaymentForm' => $updatePaymentForm->createView(),
                'choosePlanForm' => $choosePlanForm->createView(),
                'inactiveSubscriptionForm' => $inactiveSubscriptionForm ? $inactiveSubscriptionForm->createView(): null,
                'type' => $type,
                'card' => $card,
                'update' => $update,
                'city' => $city,
                'subscription' => $city->getSubscription(),
            ]);
        } else {
            $update = $request->get('update');
            if ($city->getSubscription() && $city->getSubscription()->getIsPaid() == false && $update == null) {
                $update = 'payment';
            } elseif ($city->getSubscription() && $city->getSubscription()->isCancelled() && $update == null) {
                $update = 'subscription';
            }

            return $this->render('city/subscription/contact_admin.html.twig', [
                'user' => $this->getUser(),
                'update' => $update,
                'city' => $city,
                'admin' => $city->getAdminCityUser(),
            ]);
        }
    }

    /**
     * @Route("/city/{slug}/subscription-plan/pay", name="city_subscription_plan_pay")
     * @param City $city
     * @param Request $request
     * @param RouterInterface $router
     * @param SessionInterface $session
     * @param SubscriptionProcessorInterface $subscriptionProcessor
     * @param SubscriptionManager $subscriptionManager
     * @return RedirectResponse
     */
    public function payInactiveSubscription(City $city, Request $request, RouterInterface $router, SessionInterface $session,  SubscriptionProcessorInterface $subscriptionProcessor, SubscriptionManager $subscriptionManager) {
        $this->denyAccessUnlessGranted('edit_subscription', $this->getUser());
        /** @var CityUser $user */
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
                        $subscriptionManager->setIsPaid($city->getSubscription());
                        $em = $this->getDoctrine()->getManager();
                        $user->setRawStripeCustomer($isUpdated);
                        $em->persist($user);
                        $em->flush();
                        $session->set('subscriptionNotPaid', false);
                        $this->addFlash('success', 'Successfully paid for subscription');
                        return $this->redirectToRoute('city_subscription', ['slug' => $city->getSlug()]);
                    } else {
                        $this->addFlash('error', 'Unable to complete payment, please try again.');
                        return $this->redirect($request->headers->get('referer'));
                    }


                }
            } else {
                $this->addFlash('error', 'Unable to complete payment, please try again.');
            }
        }

        return $this->redirect($request->headers->get('referer'));




    }

    /**
     * @Route("/city/{slug}/subscription-plan/choose", name="city_choose_subscription_plan")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     * @param City $city
     * @param RouterInterface $router
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function choosePlan(Request $request, City $city, RouterInterface $router, SessionInterface $session, SubscriptionProcessorInterface $subscriptionProcessor, SubscriptionManager $subscriptionManager)
    {
        // cannot use denyAccessUnlessGranted here, because would be incompatible with implementation of SubscriptionStatusVoter
        // in the context of selecting / reactivating your City's subscription
        if (false == $this->getUser() instanceof CityUser || $this->getUser()->getCity() != $city) {
            return new RedirectResponse($router->generate('access-denied'));
        }
        /** @var CityUser $user */
        $user = $this->getUser();

        $data = null;
        if ($city->getSubscription() && ($city->getSubscription()->getPaymentProcessorSubscriptionId() != '' or $city->getSubscription()->getSubscriptionPlan()->getPriceByFTE($city->getCountFTE()) <= 0.0)) {
            $data = ['subscriptionPlans' => $city->getSubscription()->getSubscriptionPlan()];
        }
        $form = $this->createForm(StripePaymentType::class, $data, [
            'city' => $city
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $token = $request->get('stripeToken');
            $formData = $form->getData();
            /** @var CitySubscriptionPlan $citySubscriptionPlan */
            $citySubscriptionPlan = $formData['subscriptionPlans'];

            $isSubscribed = false;
            $isPaid = false;
            $expiresOn = null;
            if (!$citySubscriptionPlan->getIsActive()) {
                $this->addFlash('error', 'This subscription is no longer active. Please choose an active subscription and try again.');
                return $this->redirectToRoute('job_seeker_subscription');
            }

            // If it's an upgrade, process Subscription right now
            if ($subscriptionManager->isUpgrade($city->getSubscription(), $citySubscriptionPlan)) {
                $priceByFTE = $citySubscriptionPlan->getPriceByFTE($city->getCountFTE());
                $floatValuePriceByFTE = floatval($priceByFTE);
                $id = $city->getSubscriptionId();

                $statusArray = $subscriptionManager->processCitySubscription($city, $citySubscriptionPlan, $session->getFlashBag(), $token);
                extract($statusArray);

                if ($isSubscribed) {
                    $subscriptionManager->subscribeCity($city, $citySubscriptionPlan, $isPaid, $expiresOn);
                    $session->set('subscriptionExpired', false);

                    if ($isPaid) {
                        $this->addFlash('success', 'You have subscribed to ' . $citySubscriptionPlan->getName());
                        $session->set('subscriptionNotPaid', false);
                        return $this->redirectToRoute('city_subscription', ['slug' => $city->getSlug()]);
                    } else {
                        $this->addFlash('error', 'There was an issue with your payment, please try again.');
                        return $this->redirectToRoute('city_subscription', ['slug' => $city->getSlug(), 'update' => 'payment']);
                    }
                } else {
                    $this->addFlash('error', 'Unable to subscribe to ' . $citySubscriptionPlan->getName());
                }
            } else {
                //Queue up Stripe subscription charge and change SubscriptionPlan in CGJ DB now
                $subscriptionManager->queueSubscriptionChange($city->getSubscription(), $citySubscriptionPlan);
                $subscriptionManager->changeSubscriptionLocally($city->getSubscription(), $citySubscriptionPlan);
                $this->addFlash('success', 'You have queued a subscription change');
                return $this->redirectToRoute('city_subscription', ['slug' => $city->getSlug()]);
            }
        }

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     *
     * @Route("/city/{slug}/reactivate/{id}", name="city_reactivate_subscription")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     * @param City $city
     * @param CitySubscriptionPlan $citySubscriptionPlan
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function reactivate(Request $request, City $city, CitySubscriptionPlan $citySubscriptionPlan, SubscriptionManager $subscriptionManager, SubscriptionProcessorInterface $subscriptionProcessor,SessionInterface $session)
    {
        $this->denyAccessUnlessGranted('reactivate', $citySubscriptionPlan);

        if (!$citySubscriptionPlan->getIsActive()) {
            $this->addFlash('error', 'This subscription is no longer active. Please choose an active subscription and try again.');
            return $this->redirectToRoute('job_seeker_subscription');
        }

        /** @var CityUser $user */
        $user = $this->getUser();
        $isPaid = false;
        $expiresOn = null;
        if ($city->getSubscriptionId() != '') {
            $subscriptionProcessor->setFlashBag($session->getFlashBag());
            $subscription = $subscriptionProcessor->reactivateSubscription($user, $city->getSubscriptionId());
            if ($subscription) {
                $isPaid = $subscription->status == 'active' || $subscription->status == 'cancelled' || $subscription->status == 'trialing' ? true : false;
                $timestamp = $subscription->current_period_end;
                $expiresOn = new \DateTime('@'. $timestamp);
                $city->getSubscription()->setExpiresAt($expiresOn);
                $em = $this->getDoctrine()->getManager();
                $user->setRawStripeCustomer($subscription->customer);
                $city->getSubscription()->setRawStripeSubscription($subscription->id);
                $em->persist($city->getSubscription());
                $em->persist($user);
                $em->flush();
            }
        } else if ($citySubscriptionPlan->getPriceByFTE($city->getCountFTE()) <= 0.0) {
            $isPaid = true;
        }


        if ($isPaid) {
            $subscriptionManager->reactivateCitySubscription($city, $citySubscriptionPlan, $isPaid, $expiresOn);
            $session->set('subscriptionExpired', false);
            $session->set('subscriptionNotPaid', false);
            $this->addFlash('success', 'You have reactivated '.$citySubscriptionPlan->getName());
        } else {
            $this->addFlash('error', 'Unable to reactivate subscription to ' . $citySubscriptionPlan->getName());
        }
        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/city/{slug}/payment-method/update", name="city_update_payment_method")
     * @param City $city
     * @param Request $request
     * @param RouterInterface $router
     * @param SessionInterface $session
     * @param StripeSubscriptionProcessor $subscriptionProcessor
     * @param SubscriptionManager $subscriptionManager
     * @return RedirectResponse|Response
     */
    public function updatePaymentMethod(City $city, Request $request, RouterInterface $router, SessionInterface $session,  SubscriptionProcessorInterface $subscriptionProcessor, SubscriptionManager $subscriptionManager) {

        $this->denyAccessUnlessGranted('edit', $city);

        /** @var CityUser $user */
        $user = $this->getUser();

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
                    return $this->redirectToRoute('city_subscription', ['slug' => $city->getSlug()]);
                }
            } else {
                $this->addFlash('error', 'Invalid Payment Method, please try again.');
            }
        }

        return $this->redirect($request->headers->get('referer'));
    }


    /**
     * @Route("/city/{slug}/transaction-history", name="city_transaction_history")
     * @param Request $request
     * @param City $city
     * @param SubscriptionProcessorInterface $subscriptionProcessor
     *
     * @return Response
     */
    public function displayTransactionHistory(Request $request, City $city, SubscriptionProcessorInterface $subscriptionProcessor) {

        /** @var CityUser $user */
        $user = $this->getUser();

        $subscriptionProcessor->setFlashBag($request->getSession()->getFlashBag());
        $invoices = $subscriptionProcessor->retrieveInvoices($user->getCustomerId());

        return $this->render('invoice/subscription_transaction_history.html.twig', [
            'invoices' => $invoices,
            'city' => $city,
        ]);

    }

    /**
     * @Route("/city/{slug}/invoice/{invoiceNumber}/print", name="city_transaction_invoice_print")
     * @param Request $request
     * @param City $city
     * @param $invoiceId
     * @param SubscriptionProcessorInterface $subscriptionProcessor
     *
     * @return Response
     */
    public function printInvoice(City $city, $invoiceNumber, Request $request, SubscriptionProcessorInterface $subscriptionProcessor)
    {
        $this->denyAccessUnlessGranted('view', $city);

        /** @var CityUser $user */
        $user = $this->getUser();

        $subscriptionProcessor->setFlashBag($request->getSession()->getFlashBag());
        $invoices = $subscriptionProcessor->retrieveInvoices($user->getCustomerId());

        $invoice = null;
        foreach ($invoices as $i) {
            if ($i->getNumber() == $invoiceNumber) {
                $invoice = $i;
                break;
            }
        }

        return $this->render('printable_invoice.html.twig', [
            'invoice' => $invoice,
            'city'    => $city,
        ]);
    }


    /**
     * @Route("/preview/subscription/plan/{id}/change", name="preview_subscription_plan_change")
     * @param Request $request
     * @param City $city
     * @param SubscriptionPlan $subscriptionPlan
     * @param SubscriptionProcessorInterface $subscriptionProcessor
     */
    public function previewInvoice(Request $request, SubscriptionPlan $subscriptionPlan, SubscriptionProcessorInterface $subscriptionProcessor) {

        $subscriptionProcessor->setFlashBag($request->getSession()->getFlashBag());
        if ($subscriptionPlan instanceof CitySubscriptionPlan) {
            /** @var CityUser $user */
            $user = $this->getUser();
            $city = $user->getCity();
            $currentPlan = $city->getSubscription()->getSubscriptionPlan();

            $previewedInvoice = $subscriptionProcessor->previewUpcomingInvoice($user, $city->getSubscription()->getPaymentProcessorSubscriptionId(), $subscriptionPlan, 'city-membership');
        } else {
            /** @var JobSeekerUser $user */
            $user = $this->getUser();
            $currentPlan = $user->getSubscription()->getSubscriptionPlan();
            $previewedInvoice = $subscriptionProcessor->previewUpcomingInvoice($user, $user->getSubscription()->getPaymentProcessorSubscriptionId(), $subscriptionPlan, 'job-seeker-membership');
        }


        $prorationDate = new \DateTime();
        $prorationDate->setTime(0, 0, 0, 0);

        $cost = 0;
        if ($previewedInvoice) {

            // If plan is changing frequencies than the upcoming invoice already has the amount.
            if ($currentPlan->getRenewalFrequency()->getId() != $subscriptionPlan->getRenewalFrequency()->getId()) {
                $cost = $previewedInvoice->amount_due;
            } else {
                // Calculate the proration cost:
                $current_prorations = [];
                foreach ($previewedInvoice->lines->data as $line) {
                    $unusedTime = strpos($line->description, 'Unused');
                    if ($line->type == 'subscription' || $unusedTime !== false) {
                        array_push($current_prorations, $line);
                        $cost += $line->amount;
                    }
                }
            }

        }
        return new JsonResponse(['cost' => $cost/100]);
    }

    /**
     * @Route("/cancel/subscription/request/{id}", name="cancel_subscription_request")
     * @param Request $request
     * @param SubscriptionChangeRequest $subscriptionChangeRequest
     * @param SubscriptionManager $subscriptionManager
     */
    public function cancelSubscriptionDowngradeRequest(Request $request, SubscriptionChangeRequest $subscriptionChangeRequest, SubscriptionManager $subscriptionManager) {
        $this->denyAccessUnlessGranted('edit_subscription', $this->getUser());

        $subscription = $subscriptionChangeRequest->getSubscription();


        if ($subscriptionManager->cancelSubscriptionRequest($subscriptionChangeRequest, false, true)) {
            $this->addFlash('success', 'You have successfully cancelled your downgrade subscription request.');
        } else {
            $this->addFlash('error', 'Unable to cancel your downgrade subscription request, please try again.');
        }

        if ($subscription instanceof Subscription) {
            $city = $subscriptionChangeRequest->getSubscription()->getCity();
            return $this->redirectToRoute('city_subscription', ['slug' => $city->getSlug()]);
        }

        return $this->redirectToRoute('job_seeker_subscription');


    }

}
