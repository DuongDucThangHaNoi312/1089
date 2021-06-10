<?php

namespace App\Controller\City;

use App\Entity\City;
use App\Entity\CityCityUser;
use App\Entity\CityRegistration;
use App\Entity\SubscriptionPlan\CitySubscriptionPlan;
use App\Entity\User\CityUser;
use App\Form\City\Registration\FindCityType;
use App\Form\City\Registration\PasscodeType;
use App\Form\City\Registration\ResetPasswordType;
use App\Form\City\Registration\StepOneType;
use App\Form\City\Registration\StepTwoType;
use App\Form\City\Registration\TrialType;
use App\Form\City\Registration\CityRegistrationType;
use App\Service\LocationGetter;
use App\Service\SubscriptionManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Repository\CityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Translation\TranslatorInterface;
use App\Entity\CityRegistration\Lookup\CityRegistrationStatus;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class RegistrationController extends AbstractController
{

    private $eventDispatcher;
    private $userManager;
    private $translator;

    public function __construct(EventDispatcherInterface $eventDispatcher, UserManagerInterface $userManager, TranslatorInterface $translator)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->userManager = $userManager;
        $this->translator = $translator;
    }


    /**
     * @param Request $request
     * @return RedirectResponse|Response
     * @Route("/registration/find/city", name="city_registration_find_city")
     */
    public function findCity(Request $request, LocationGetter $locationGetter)
    {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            $this->addFlash('warning', 'You are already logged in and cannot register a new city!');
            return $this->redirectToRoute('error');
        }

        $form = $this->createForm(FindCityType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $data          = $form->get('city')->getData();
                $dataLocations = explode('_', $data);
                $cityId        = $dataLocations[0];
                $city          = $locationGetter->getLocation($cityId, City::class);
                if ($city) {
                    $slug = $city->getSlug();
                    if ($city->getIsRegistered()) {
                        return $this->redirectToRoute('city_registration_city_registered', ['city_slug' => $slug]);
                    } elseif ($city->hasPendingRegistration()) {
                        return $this->redirectToRoute('city_registration_city_pending', ['city_slug' => $slug]);
                    } else {
                        return $this->redirectToRoute('city_registration_step_one', ['city_slug' => $slug]);
                    }
                }
            } else {
                $errMsg = $this->translator->trans('form.invalid');
                $this->addFlash('error', $errMsg);

                foreach ($form->getErrors() as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }

        return $this->render('city/registration/find_city.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/registration/city/{city_slug}/registered", name="city_registration_city_registered")
     */
    public function cityRegistered(Request $request)
    {
        return $this->render('city/registration/city_registered.html.twig');
    }

    /**
     * @Route("/registration/city/{city_slug}/pending", name="city_registration_city_pending")
     */
    public function cityPending(Request $request)
    {
        return $this->render('city/registration/city_pending.html.twig');
    }

    /**
     * @Route("/registration/city/{city_slug}/step/one", name="city_registration_step_one")
     * @ParamConverter("city", options={"mapping"={"city_slug"="slug"}})
     */
    public function stepOne(Request $request, City $city)
    {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            $this->addFlash('warning', 'You are already logged in and cannot register!');
            return $this->redirectToRoute('error');
        }

        if ($city->getIsRegistered()) {
            return $this->redirectToRoute('city_registration_city_registered', ['city_slug' => $city->getSlug()]);
        } elseif ($city->hasPendingRegistration()) {
            return $this->redirectToRoute('city_registration_city_pending', ['city_slug' => $city->getSlug()]);
        }

        $cityUser = new CityUser();
        $cityUser->setEnabled(true);

        $event = new GetResponseUserEvent($cityUser, $request);
        $this->eventDispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $form = $this->createForm(StepOneType::class);
        $form->setData($cityUser);

        $form->handleRequest($request);

        if($form->isSubmitted()) {
            if ($form->isValid()) {
                /** @var CityUser $data */
                $data = $form->getData();
                $cityUser->setUsername($data->getEmail());
                $cityUser->addRole('ROLE_PENDING_CITYUSER');
                $cityUser->addRole('ROLE_PENDING_CITYADMIN');

                $event = new FormEvent($form, $request);
                $this->eventDispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);

                $this->userManager->updateUser($cityUser);

                if (null === $response = $event->getResponse()) {
                    $url = $this->generateUrl('city_registration_step_one_verify', ['city_slug' => $city->getSlug()]);
                    $response = new RedirectResponse($url);
                }

                return $response;
            }
            $event = new FormEvent($form, $request);
            $this->eventDispatcher->dispatch(FOSUserEvents::REGISTRATION_FAILURE, $event);

            if (null !== $response = $event->getResponse()) {
                return $response;
            }
        }

        return $this->render('city/registration/step_one.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/registration/city/{city_slug}/step/one/verify", name="city_registration_step_one_verify")
     * @ParamConverter("city", options={"mapping"={"city_slug"="slug"}})
     */
    public function stepOneVerify(Request $request, City $city)
    {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            $this->addFlash('warning', 'You are already logged in and cannot register!');
            return $this->redirectToRoute('error');
        }

        if ($city->getIsRegistered()) {
            return $this->redirectToRoute('city_registration_city_registered', ['city_slug' => $city->getSlug()]);
        } elseif ($city->hasPendingRegistration()) {
            return $this->redirectToRoute('city_registration_city_pending', ['city_slug' => $city->getSlug()]);
        }
        $request->getSession()->remove('fos_user_send_confirmation_email/email');

        return $this->render('city/registration/step_one_verify.html.twig');
    }

    /**
     * @Route("/registration/city/{city_slug}/step/one/confirm/{token}", name="city_registration_step_one_confirm")
     * @ParamConverter("city", options={"mapping"={"city_slug"="slug"}})
     */
    public function stepOneConfirm(Request $request,City $city, $token) {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY') && $this->isGranted('ROLE_CITYUSER')) {
            $this->addFlash('warning', 'You are already logged in and cannot register!');
            return $this->redirectToRoute('error');
        }

        $userManager = $this->userManager;

        /** @var CityUser $user */
        $user = $userManager->findUserByConfirmationToken($token);

        if (null === $user) {
            if ($this->getUser() == null) {
                throw new NotFoundHttpException(sprintf('The user with confirmation token "%s" does not exist', $token));
            } else {
                $user = $this->getUser();
            }
        }

        /** If a user is trying to register as an Admin of the City they will have an extra role of Pending City Admin unlike if they are an invited User by an Admin. */
        if ($user->hasRole('ROLE_PENDING_CITYADMIN')) {
            if ($city->getIsRegistered()) {
                return $this->redirectToRoute('city_registration_city_registered', ['city_slug' => $city->getSlug()]);
            } elseif ($city->hasPendingRegistration()) {
                return $this->redirectToRoute('city_registration_city_pending', ['city_slug' => $city->getSlug()]);
            }
        }

        $user->setConfirmationToken(null);
        $user->setEnabled(true);

        $event = new GetResponseUserEvent($user, $request);
        $this->eventDispatcher->dispatch(FOSUserEvents::REGISTRATION_CONFIRM, $event);

        $userManager->updateUser($user);

        /**  If a user is trying to register as an Admin of the City they will have an extra role of Pending City Admin unlike if they are an invited User by an Admin. */
        /** @var CityUser $user */
        if ($user->hasRole('ROLE_PENDING_CITYADMIN')) {
            if (null === $response = $event->getResponse()) {
                $url = $this->generateUrl('city_registration_step_two', ['city_slug' => $city->getSlug()]);
                $response = new RedirectResponse($url);
            }
        } else {
            $url = $this->generateUrl('city_registration_step_one_reset_password', ['city_slug' => $city->getSlug()]);
            $response = new RedirectResponse($url);

        }

        $this->eventDispatcher->dispatch(FOSUserEvents::REGISTRATION_CONFIRMED, new FilterUserResponseEvent($user, $request, $response));

        return $response;
    }

    /**
     * @Route("/registration/city/{city_slug}/step/one/reset/password", name="city_registration_step_one_reset_password")
     * @ParamConverter("city", options={"mapping"={"city_slug"="slug"}})
     */
    public function stepResetPassword(Request $request, City $city) {
        $this->denyAccessUnlessGranted('ROLE_PENDING_CITYUSER');

        /** @var CityUser $cityUser */
        $cityUser = $this->getUser();
        $form = $this->createForm(ResetPasswordType::class);
        $form->setData($cityUser);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var CityUser $data */
            $data = $form->getData();
            $data->setRoles(['ROLE_CITYUSER']);
            $this->userManager->updateUser($data);
            $url = $this->generateUrl('city_dashboard');
            $response = new RedirectResponse($url);
            return $response;
        }

        return $this->render('city/registration/step_one_reset_password.html.twig', [
            'form' => $form->createView(),
            'city_slug' => $city->getSlug(),
        ]);
    }

    /**
     * @Route("/registration/city/{city_slug}/step/two", name="city_registration_step_two")
     * @ParamConverter("city", options={"mapping"={"city_slug"="slug"}})
     */
    public function stepTwo(Request $request, City $city) {
        $this->denyAccessUnlessGranted('ROLE_PENDING_CITYUSER');

        if ($city->getIsRegistered()) {
            return $this->redirectToRoute('city_registration_city_registered', ['city_slug' => $city->getSlug()]);
        } elseif ($city->hasPendingRegistration()) {
            return $this->redirectToRoute('city_registration_city_pending', ['city_slug' => $city->getSlug()]);
        }

        $form = $this->createForm(StepTwoType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            /** @var CityUser $user */
            $agreeTermsOfUseAgreement = $form['agreeTermsOfUseAgreement']->getData();
            if ($agreeTermsOfUseAgreement == true) {
                $user = $this->getUser();
                $user->setFirstname($data['firstName']);
                $user->setLastname($data['lastName']);
                $user->setPhone($data['phone']);
                $em = $this->getDoctrine()->getManager();

                $cityRegistration = new CityRegistration();
                $cityRegistration->setJobTitle($data['jobTitle']);
                $cityRegistration->setDepartment($data['department']);
                $cityRegistration->setCity($city);
                $cityRegistration->setCityUser($user);
                $em->persist($cityRegistration);

                $em->flush();

                $request->getSession()->set('city_registration_id', $cityRegistration->getId());
                $url = $this->generateUrl('city_registration_step_three', ['city_slug' => $city->getSlug()]);
                $response = new RedirectResponse($url);
                return $response;
            } else {
                $this->addFlash('error', 'Please accept Terms of Use Agreement before continuing to the next step.');
            }
        }

        return $this->render('city/registration/step_two.html.twig', [
            'form' => $form->createView(),
            'city_slug' => $city->getSlug(),
        ]);
    }

    /**
     * @Route("/registration/city/{city_slug}/step/three", name="city_registration_step_three")
     * @ParamConverter("city", options={"mapping"={"city_slug"="slug"}})
     * @param Request $request
     * @param City $city
     * @return RedirectResponse|Response
     * @throws \Exception
     */
    public function stepThree(Request $request, City $city, SubscriptionManager $subscriptionManager) {
        $this->denyAccessUnlessGranted('ROLE_PENDING_CITYUSER');

        if ($city->getIsRegistered()) {
            return $this->redirectToRoute('city_registration_city_registered', ['city_slug' => $city->getSlug()]);
        } elseif ($city->hasPendingRegistration()) {
            return $this->redirectToRoute('city_registration_city_pending', ['city_slug' => $city->getSlug()]);
        }

        $cityRegistration = new CityRegistration();
        if ($city_registration_id = $request->getSession()->get('city_registration_id')) {
            $cityRegistration = $this->getDoctrine()
                ->getRepository(CityRegistration::class)
                ->find($city_registration_id);
        }
        $user = $this->getUser();

        $form = $this->createForm(PasscodeType::class, $cityRegistration);
        $form->handleRequest($request);

        $passcodeInvalid = false;
        if($form->isSubmitted() && $form->isValid()) {
            if ($cityRegistration->getPasscode()) {
               if ($city && $city->isPasscodeValid($cityRegistration->getPasscode())) {

                   // passcode is valid, so complete setup of city account

                   /** @var CityRegistration\Lookup\CityRegistrationStatus $approvedStatus */
                   $approvedStatus = $this->getDoctrine()
                       ->getRepository(CityRegistrationStatus::class)
                       ->findOneBy(['slug' => CityRegistrationStatus::APPROVED_STATUS]);

                   // give user proper role
                   $user->setRoles(['ROLE_CITYUSER', 'ROLE_CITYADMIN']);
                   $user->setCity($city);

                   // confirm the registration
                   $cityRegistration->setCity($city);
                   $cityRegistration->setCityUser($user);
                   $cityRegistration->setStatus($approvedStatus);
                   $cityRegistration->setExplanation('The user\'s registration was automatically approved because they input the city\'s passcode.');
                   $cityRegistration->setDecisionDate(new \DateTime());

                   // add initial city user
                   $cityCityUser = new CityCityUser();
                   $cityCityUser->setCityUser($user);

                   // set currentStars to 5 for city is registered
                   $city->setCurrentStars(City::MAX_STARS);

                   // indicate city is registered and set up default subscription
                   $city->setIsValidated(true);
                   $city->setIsRegistered(true);
                   $city->setAdminCityUser($user);
                   $city->addCityCityUser($cityCityUser);

                   $citySubscriptionPlan = $this->getDoctrine()->getRepository(CitySubscriptionPlan::class)->find(CitySubscriptionPlan::CITY_TRIAL_PLAN_ID);
                   $subscriptionManager->subscribeCity($city, $citySubscriptionPlan, true);

                   $em = $this->getDoctrine()->getManager();
                   $em->persist($user);
                   $em->persist($cityRegistration);
                   $em->persist($city);
                   $em->flush();

                   $request->getSession()->remove('city_registration_id');

                   $url = $this->generateUrl('city_registration_step_three_validated', ['city_slug' => $city->getSlug()]);
                   $response = new RedirectResponse($url);
                   return $response;
               } else {
                   $passcodeInvalid = true;
               }
            } else {
                $url = $this->generateUrl('city_registration_step_three_verification', ['city_slug' => $city->getSlug()]);
                $response = new RedirectResponse($url);
                return $response;
            }
        }

        return $this->render('city/registration/step_three.html.twig', [
            'form' => $form->createView(),
            'city_slug' => $city->getSlug(),
            'invalid' => $passcodeInvalid
        ]);
    }

    /**
     * @Route("/registration/city/{city_slug}/step/three/verification", name="city_registration_step_three_verification")
     * @ParamConverter("city", options={"mapping"={"city_slug"="slug"}})
     */
    public function stepThreeVerification(Request $request, City $city) {
        $this->denyAccessUnlessGranted('ROLE_PENDING_CITYUSER');

        if ($city->getIsRegistered()) {
            return $this->redirectToRoute('city_registration_city_registered', ['city_slug' => $city->getSlug()]);
        } elseif ($city->hasPendingRegistration()) {
            return $this->redirectToRoute('city_registration_city_pending', ['city_slug' => $city->getSlug()]);
        }

        $user = $this->getUser();
        $cityRegistration = new CityRegistration();
        if ($city_registration_id = $request->getSession()->get('city_registration_id')) {
            $cityRegistration = $this->getDoctrine()
                ->getRepository(CityRegistration::class)
                ->find($city_registration_id);
        }

        $form = $this->createForm(CityRegistrationType::class, $cityRegistration);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $cityRegistration = $form->getData();

            $pendingStatus = $this->getDoctrine()
                ->getRepository(CityRegistrationStatus::class)
                ->findOneBy(['slug' => CityRegistrationStatus::PENDING_STATUS]);

            $cityRegistration->setCity($city);
            $cityRegistration->setCityUser($user);
            $cityRegistration->setStatus($pendingStatus);

            $em = $this->getDoctrine()->getManager();
            $em->persist($cityRegistration);
            $em->flush();

            $request->getSession()->remove('city_registration_id');

            $url = $this->generateUrl('city_registration_awaiting_verification', ['city_slug' => $city->getSlug()]);
            $response = new RedirectResponse($url);
            return $response;
        }

        return $this->render('city/registration/step_three_verification.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/registration/city/{city_slug}/step/three/verifying", name="city_registration_awaiting_verification")
     * @ParamConverter("city", options={"mapping"={"city_slug"="slug"}})
     */
    public function stepThreeVerifying(Request $request, City $city) {
        $session = new Session();
        $session->invalidate();
        $this->denyAccessUnlessGranted('ROLE_PENDING_CITYUSER');
        return $this->render('city/registration/step_three_verifying.html.twig');
    }

    /**
     * @Route("/registration/city/{city_slug}/step/three/validated", name="city_registration_step_three_validated")
     * @ParamConverter("city", options={"mapping"={"city_slug"="slug"}})
     */
    public function stepThreeValidated(Request $request, City $city) {
        $this->denyAccessUnlessGranted('ROLE_CITYUSER');

        $form = $this->createForm(TrialType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute('city_dashboard');
        }

        return $this->render('city/registration/step_three_validated.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param null $id
     * @param null $slug
     * @return City|City[]|null
     */
    public function  getCity($id = null, $slug = null) {
        /** @var CityRepository $repository */
        $repository = $this->getDoctrine()->getRepository(City::class);
        $city = $repository->find($id);
        return $city;
    }
}
