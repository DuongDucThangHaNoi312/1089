<?php

namespace App\Controller\JobSeeker;

use App\Entity\City;
use App\Entity\JobTitle\Lookup\JobCategory;
use App\Entity\JobTitle\Lookup\JobTitleName;
use App\Entity\User;
use App\Form\JobSeeker\Registration\JobSeekerProfileType;
use App\Service\JobSeekerRegistrationSource;
use App\Service\LocationGetter;
use App\Entity\SubscriptionPlan\JobSeekerSubscriptionPlan;
use App\Entity\User\JobSeekerUser;
use App\Entity\User\SavedCity;
use App\Form\JobSeeker\Registration\StepOneType;
use App\Service\SavedSearchHelper;
use App\Service\SubscriptionManager;
use Exception;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class RegistrationController
 * @package App\Controller\JobSeeker
 */
class RegistrationController extends AbstractController {
    private $eventDispatcher;
    private $userManager;
    private $translator;

    /**
     * RegistrationController constructor.
     * @param EventDispatcherInterface $eventDispatcher
     * @param UserManagerInterface $userManager
     * @param TranslatorInterface $translator
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, UserManagerInterface $userManager, TranslatorInterface $translator)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->userManager = $userManager;
        $this->translator = $translator;
    }

    /**
     * @Route("/registration/job-seeker/step/one", name="job_seeker_registration_step_one")
     * @param Request $request
     * @return RedirectResponse|Response|null
     */
    public function stepOne(Request $request, SubscriptionManager $subscriptionManager, JobSeekerRegistrationSource $jobSeekerRegistrationSource)
    {
        if ($request->get('source') && $request->get('source') === 'link_to_job') {
            $this->addFlash('warning', 'Please register before continuing.');
        }
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            $this->addFlash('warning', 'You have already registered.');
            return $this->redirectToRoute('error');
        }

        $jobSeekerUser = new JobSeekerUser();
        $jobSeekerUser->setEnabled(true);

        $event = new GetResponseUserEvent($jobSeekerUser, $request);
        $this->eventDispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $form = $this->createForm(StepOneType::class);
        $form->setData($jobSeekerUser);
        $form->handleRequest($request);

        $routeParams = $request->query->all();
        $isApplyLink = in_array('view_job_alert_apply_link', $routeParams) ? true : false;
        if ($form->isSubmitted()) {
            if($form->isValid()) {
                /** @var JobSeekerUser $data */
                $data           = $form->getData();

                $jobSeekerUser->setUsername($data->getEmail());
                $jobSeekerUser->setEnabled(true);
                $jobSeekerUser->addRole('ROLE_PENDING_JOBSEEKER');

                $this->userManager->updateUser($jobSeekerUser);

                // CIT-807: Had to create a trial subscription plan at this point.
                $jobSeekerTrialSubscriptionPlan = $this->getDoctrine()->getRepository(JobSeekerSubscriptionPlan::class)
                    ->find(JobSeekerSubscriptionPlan::JOB_SEEKER_TRIAL_PLAN_ID);
                $subscriptionManager->subscribeJobSeeker($jobSeekerUser, $jobSeekerTrialSubscriptionPlan, true, true);

                $event = new FormEvent($form, $request);
                $this->eventDispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);
                $this->addFlash('warning', 'Please complete Registration before searching Jobs.');

                if(null === $response = $event->getResponse()) {
                    if (array_key_exists('dest_url',$routeParams) && $routeParams['dest_url']) {
                        $url = $routeParams['dest_url'];
                    } else {
                        $url = $this->generateUrl('job_seeker_registration_step_one_verify');
                    }
                    $response = new RedirectResponse($url);
                }

                $this->eventDispatcher->dispatch(FOSUserEvents::REGISTRATION_COMPLETED, new FilterUserResponseEvent($jobSeekerUser, $request, $response));
                return $response;
            } else {
                $errMsg = $this->translator->trans('form.invalid');
                $this->addFlash('error', $errMsg);

                foreach ($form->getErrors() as $error) {
                    $this->addFlash('error', $error->getMessage());
                }

                $event = new FormEvent($form, $request);
                $this->eventDispatcher->dispatch(FOSUserEvents::REGISTRATION_FAILURE, $event);

                if(null !== $response = $event->getResponse()) {
                    return $response;
                }
            }
        }


        $source = (array_key_exists('source',$routeParams) && $routeParams['source']) ? $routeParams['source'] :  '';
        $description = JobSeekerRegistrationSource::description($source);
        $button = JobSeekerRegistrationSource::button($source);
        $heading = JobSeekerRegistrationSource::heading($source);


        return $this->render('job_seeker/registration/step_one.html.twig', [
            'form'        => $form->createView(),
            'description' => $description,
            'button'      => $button,
            'heading'     => $heading,
            'isApplyLink' => $isApplyLink
        ]);
    }

    /**
     * @Route("/registration/job-seeker/step/one/verify", name="job_seeker_registration_step_one_verify")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function stepOneVerify(Request $request) {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            $this->addFlash('warning', 'Please login before continuing.');
            return $this->redirectToRoute('fos_user_security_login');
        }

        $request->getSession()->remove('fos_user_send_confirmation_email/email');

        /** @var User $loggedInUser */
        $loggedInUser = $this->getUser();
        if ($loggedInUser->getConfirmationToken() == null) {
            $this->addFlash('success', "You've already confirmed your email.");
            $isJobSeeker = $this->isGranted('ROLE_JOBSEEKER');
            $url = $this->generateUrl('job_seeker_registration_step_two');
            if ($isJobSeeker) {
                $url = $this->generateUrl('job_seeker_dashboard');
            }
            return $this->redirect($url);
        }

        return $this->render('job_seeker/registration/step_one_verify.html.twig');
    }

    /**
     * @Route("/registration/job-seeker/step/one/confirm/{token}", name="job_seeker_registration_step_one_confirm")
     */
    public function stepOneConfirm(Request $request, $token) {

        $isAuthenticated = $this->isGranted('IS_AUTHENTICATED_FULLY');

        if ($isAuthenticated) {
            /** @var User $loggedInUser */
            $loggedInUser = $this->getUser();
            if ($loggedInUser->getConfirmationToken() == null) {
                $this->addFlash('success', "You've already confirmed your email.");
                $isJobSeeker = $this->isGranted('ROLE_JOBSEEKER');
                $url = $this->generateUrl('job_seeker_registration_step_two');
                if ($isJobSeeker) {
                    $url = $this->generateUrl('job_seeker_dashboard');
                }
                return $this->redirect($url);
            }

            //return $this->redirectToRoute('job_seeker_registration_step_one_verify');
        }

        $userManager = $this->userManager;
        $user = $userManager->findUserByConfirmationToken($token);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with confirmation token "%s" does not exist', $token));
        }

        // CIT-807: If Authenticated Fully gather the user record.
        if ($isAuthenticated) {
            $loggedInUser = $this->getUser();
            if ($loggedInUser->getId() === $user->getId()) {
                $user->setConfirmationToken(null);
            } else {
                $this->addFlash('error', 'You cannot confirm another users email.');
                return $this->redirectToRoute('job_seeker_registration_step_one_verify');
            }
        } else {
            $user->setConfirmationToken(null);
        }

        $this->addFlash('success', "You've successfully confirmed your email");

//        $user->setEnabled(true);

        $event = new GetResponseUserEvent($user, $request);
        if (!$isAuthenticated) {
            $this->eventDispatcher->dispatch(FOSUserEvents::REGISTRATION_CONFIRM, $event);
        }

        $userManager->updateUser($user);

        if (null === $response = $event->getResponse()) {
            $url = $this->generateUrl('job_seeker_registration_step_two');
            $response = new RedirectResponse($url);
        }

        if (!$isAuthenticated) {
            $this->eventDispatcher->dispatch(FOSUserEvents::REGISTRATION_CONFIRMED, new FilterUserResponseEvent($user, $request, $response));
        }
        return $response;
    }


    /**
     * @Route("/registration/job-seeker/resend/confirmation", name="job_seeker_registration_resend_confirmation")
     * @param Request $request
     * @param MailerInterface $mailer
     * @param TokenGeneratorInterface $tokenGenerator
     * @return RedirectResponse
     */
    public function resendConfirmationEmail(Request $request, MailerInterface $mailer, TokenGeneratorInterface $tokenGenerator, SessionInterface $session) {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var User $user */
        $user = $this->getUser();
        if ($user->getConfirmationToken()) {
            $em = $this->getDoctrine()->getManager();
            $user->setConfirmationToken($tokenGenerator->generateToken());
            $em->persist($user);
            $em->flush();

            $mailer->sendJobSeekerRegistrationConfirmationEmailMessage($user);
            $session->set('fos_user_send_confirmation_email/email', $user->getEmail());

            $this->addFlash('success', 'Confirmation Email has been sent.');
        }
        $redirectURL = $request->headers->get('referer');
        return $this->redirect($redirectURL);
    }

    /**
     * @Route("/registration/job-seeker/step/two", name="job_seeker_registration_step_two")
     * @param Request $request
     * @param LocationGetter $locationGetter
     *
     * @return RedirectResponse|Response
     */
    public function stepTwo(Request $request, LocationGetter $locationGetter)
    {
        $this->denyAccessUnlessGranted('ROLE_PENDING_JOBSEEKER');

        /** @var JobSeekerUser $jobSeekerUser */
        $jobSeekerUser = $this->getUser();
        $form          = $this->createForm(JobSeekerProfileType::class, $jobSeekerUser, [
            'step3' => false
        ]);
        $form->setData($jobSeekerUser);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var JobSeekerUser $data */
            $jobSeekerUser = $form->getData();

            $residentLocation = $form->get('residentLocation')->getData();
            if ($residentLocation) {
                try {
                    $locationGetter->setJobSeekerLocation($jobSeekerUser, $residentLocation);
                } catch (Exception $e) {
                    $this->addFlash('error', 'Problem setting Location, please input your residence city.');
                    return $this->render('job_seeker/registration/step_two.html.twig', [
                        'form' => $form->createView(),
                    ]);
                }
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($jobSeekerUser);
            $em->flush();

            $url      = $this->generateUrl('job_seeker_registration_step_three');
            $response = new RedirectResponse($url);

            return $response;
        }

        return $this->render('job_seeker/registration/step_two.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/registration/job-seeker/step/three", name="job_seeker_registration_step_three")
     * @param Request $request
     * @param SubscriptionManager $subscriptionManager
     * @param LocationGetter $locationGetter
     *
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function stepThree(Request $request, SubscriptionManager $subscriptionManager, LocationGetter $locationGetter, SavedSearchHelper $savedSearchHelper)
    {
        $this->denyAccessUnlessGranted('ROLE_PENDING_JOBSEEKER');

        /** @var JobSeekerUser $jobSeekerUser */
        $jobSeekerUser = $this->getUser();
        $form          = $this->createForm(JobSeekerProfileType::class, $jobSeekerUser, [
            'step2' => false,
            'validation_groups' => 'Default'
        ]);
        $form->setData($jobSeekerUser);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var JobSeekerUser $data */
            $jobSeekerUser = $form->getData();
            $jobSeekerUser->setRoles(['ROLE_JOBSEEKER']);

            // save jobTitles
            $jobTitlesNameRepo = $this->getDoctrine()->getRepository(JobTitleName::class);
            $jobCategoryRepo   = $this->getDoctrine()->getRepository(JobCategory::class);

            $jobTitleNameIds    = $form->get('interestedJobTitleNames')->getData();
            $jobTitlesNames     = $jobTitlesNameRepo->findBy(['id' => $jobTitleNameIds]);
            if ($jobTitlesNames) {
                foreach ($jobTitlesNames as $jobTitlesName) {
                    $jobSeekerUser->addInterestedJobTitleName($jobTitlesName);
                }
            }

            // save jobCategories
            $jobCategoryIds   = [$form->get('interestedJobCategoryGenerals')->getData(), $form->get('interestedJobCategoryNotGenerals')->getData()];

            $jobCategories = $jobCategoryRepo->findBy(['id' => $jobCategoryIds]);
            if ($jobCategories) {
                foreach ($jobCategories as $jobCategory) {
                    $jobSeekerUser->addInterestedJobCategory($jobCategory);
                }
            }

            // CIT-423: set Works For City to Job Seeker
            $worksForCity = $form->get('worksForCity')->getData();
            if ($worksForCity) {
                $jobSeekerUser->setWorkForCityGovernment(true);
                $locationGetter->setJobSeekerWorksForLocation($jobSeekerUser, $worksForCity);
            } else {
                $jobSeekerUser->setWorkForCityGovernment(false);
            }

            $em             = $this->getDoctrine()->getManager();
            $cityRepository = $this->getDoctrine()->getRepository(City::class);

            $em->getRepository(SavedCity::class)->deleteByJobSeeker($jobSeekerUser);
            foreach ($jobSeekerUser->getInterestedCounties() as $county) {
                //Based on their interested Counties, create SaveCity records for the User
                $cities = $cityRepository->findAllByCounty($county->getId());
                foreach ($cities as $city) {
                    $savedCity = new SavedCity();
                    $savedCity->setCity($city);
                    $savedCity->setUser($jobSeekerUser);
                    $em->persist($savedCity);
                    $jobSeekerUser->addSavedCity($savedCity);
                }
            }

            $em->persist($jobSeekerUser);
            $em->flush();

            // save the default saved search immediately after user finishes this step
            $savedSearchHelper->saveDefaultSearchCriteria($jobSeekerUser);

            $url      = $this->generateUrl('job_seeker_dashboard');
            $response = new RedirectResponse($url);

            return $response;
        }


        return $this->render('job_seeker/registration/step_three.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/filter/job-titles", name="filter_job_titles")
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function filterJobTitles(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $counties         = $request->query->get('counties');
        $jobLevels        = $request->query->get('jobLevels');
        $jobTitleNameRepo = $em->getRepository(JobTitleName::class);

        $jobTitleNames = [];
        $responseData  = [];
        if ($counties && $jobLevels) {
            $jobTitleNames = $jobTitleNameRepo->findByCountiesAndJobLevel($counties, $jobLevels);
        }

        if ($jobTitleNames) {
            foreach ($jobTitleNames as $jobTitleName) {
                $responseData[$jobTitleName->getId()] = $jobTitleName->getName();
            }
        }

        return new JsonResponse($responseData);
    }
}
