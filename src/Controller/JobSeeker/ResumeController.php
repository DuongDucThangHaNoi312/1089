<?php

namespace App\Controller\JobSeeker;

use App\Entity\City;
use App\Entity\User\JobSeekerUser;
use App\Entity\User\JobSeekerUser\Resume;
use App\Form\JobSeeker\Resume\InterestProfileType;
use App\Form\JobSeeker\Resume\KeyQualificationsType;
use App\Form\JobSeeker\Resume\ResumeJobSeekerType;
use App\Form\JobSeeker\Resume\SettingsType;
use App\Form\JobSeeker\Resume\SummaryType;
use App\Form\JobSeeker\Resume\WorkHistoriesType;
use App\Repository\CityRepository;
use App\Service\LocationGetter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class ResumeController extends AbstractController
{

    /** @var LocationGetter $locationGetter */
    private $locationGetter;

    public function __construct(LocationGetter $locationGetter)
    {
        $this->locationGetter = $locationGetter;
    }

    /**
     * @Route("/job-seeker/resume", name="job_seeker_resume")
     */
    public function resume()
    {
        // CIT-807: Redirecting to registration flow. If they have not completed it.
        if ($this->isGranted('ROLE_PENDING_JOBSEEKER')) {
            $this->addFlash('warning', 'Please complete your Registration before accessing your resume');
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

        $this->denyAccessUnlessGranted('ROLE_JOBSEEKER');

        /** @var JobSeekerUser $user */
        $user = $this->getUser();
        $resume = $user->getResume();

        $toggle = 'collapse';
        if (!$resume || $resume->getIsComplete() == false) {
            $toggle = 'show';
        }

        return $this->render('job_seeker/dashboard/_resume.html.twig', [
            'toggle' => $toggle,
            'user' => $user,
            'resume' => $resume,
        ]);
    }

    /**
     * @Route("/job-seeker/resume/create", name="job_seeker_create_resume")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createResume(Request $request) {

        // CIT-807: Redirecting to registration flow. If they have not completed it.
        if ($this->isGranted('ROLE_PENDING_JOBSEEKER')) {
            $this->addFlash('warning', 'Please complete your Registration before creating your resume.');
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

        $this->denyAccessUnlessGranted('ROLE_JOBSEEKER');

        try {
            $em = $this->getDoctrine()->getManager();
            /** @var JobSeekerUser $user */
            $user = $this->getUser();
            $resume = Resume::create($user);

            $user->setResume($resume);
            $em->persist($resume);
            $em->persist($user);
            $em->flush();
        } catch(\Exception $exception) {
            $this->addFlash('error', 'Error! Unable to create Resume at this time, please try again.');
            return $this->redirect($request->headers->get('referer'));
        }

        return $this->redirectToRoute('job_seeker_edit_resume', ['id' => $resume->getId()]);
    }

    /**
     * @Route("/job-seeker/resume/add", name="job_seeker_add_resume")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addResume(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_JOBSEEKER');;

        /** @var JobSeekerUser $user */
        $user = $this->getUser();
        $resume = $user->getResume();
        if (!$resume) {
            try {
                $em = $this->getDoctrine()->getManager();

                $resume = Resume::create($user);

                $user->setResume($resume);
                $em->persist($resume);
                $em->persist($user);
                $em->flush();
                $this->addFlash('success', 'Success! Resume has been created.');
            } catch(\Exception $exception) {
                $this->addFlash('error', 'Error! Unable to create Resume at this time, please try again.');
            }
        }

        return $this->redirectToRoute('job_seeker_edit_resume', ['id' => $resume->getId()]);
    }

    /**
     * @Route("/job-seeker/resume/{id}/edit", name="job_seeker_edit_resume")
     * @param Request $request
     * @param Resume $resume
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editResume(Request $request, Resume $resume)
    {
        $this->denyAccessUnlessGranted('edit', $resume);
        /** @var JobSeekerUser $user */
        $user = $this->getUser();

        if ($user->getResume() && $user->getResume()->getId() != $resume->getId()) {
            $this->addFlash('error','Error! You cannot edit a resume that is not yours.');
            return $this->redirect($request->headers->get('referer'));
        }

        return $this->render('job_seeker/resume/index.html.twig', [
            'isEditable' => true,
            'user' => $user,
           'resume' => $resume
        ]);
    }

    /**
     * @Route("/job-seeker/resume/{id}/view", name="job_seeker_view_resume")
     * @param Request $request
     * @param Resume $resume
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewResume(Request $request, Resume $resume) {
        $this->denyAccessUnlessGranted('view', $resume);
        /** @var JobSeekerUser $user */
        $user = $this->getUser();

        return $this->render('job_seeker/resume/index.html.twig', [
            'isEditable' => false,
            'user' => $user,
            'resume' => $resume
        ]);
    }

    /**
     * @Route("/job-seeker/resume/{id}/delete", name="job_seeker_delete_resume")
     * @param Request $request
     * @param Resume $resume
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteResume(Request $request, Resume $resume)
    {
        $this->denyAccessUnlessGranted('edit', $resume);

        /** @var JobSeekerUser $user */
        $user = $this->getUser();

        if ($user->getResume() && $user->getResume()->getId() != $resume->getId()) {
            $this->addFlash('error','Error! You cannot delete a resume that is not yours.');
            return $this->redirect($request->headers->get('referer'));
        }

        try {
            $em = $this->getDoctrine()->getManager();
            $em->remove($resume);
            $em->flush();
            $this->addFlash('success', 'Success! Your Resume has been deleted successfully.');
        } catch(\Exception $exception) {
            $this->addFlash('error', 'Error! Deleting Resume, please try again.');
        }

        return $this->redirect($request->headers->get('referer'));

    }

    /**
     * @Method("POST")
     * @Route("/job-seeker/resume/{id}/edit/summary/create", name="job_seeker_edit_resume_summary_create")
     */
    public function createSummary(Request $request, Resume $resume) {
        $this->denyAccessUnlessGranted('edit', $resume);
        $form = $this->createAjaxForm($resume, SummaryType::class, 'job_seeker_edit_resume_summary_create');
        return $this->processAjaxForm($request, $resume, $form, 'job_seeker/resume/_summary_form.html.twig', 'job_seeker/resume/_summary.html.twig', 'summary');
    }

    /**
     * @Route("/job-seeker/resume/{id}/edit/summary", name="job_seeker_edit_resume_summary")
     */
    public function summary(Request $request, Resume $resume, Bool $isEditable) {
        $templateArgs = [
            'resume' => $resume
        ];

        if ($this->isGranted('edit', $resume) && $isEditable) {
            $form = $this->createAjaxForm($resume, SummaryType::class, 'job_seeker_edit_resume_summary_create');
            $templateArgs = array_merge($templateArgs, [
                'form_name' => $form->getName(),
                'form' => $form->createView(),
                'section' => ['complete' => $resume->isSectionComplete('summary')]
            ]);
        }

        return $this->render('job_seeker/resume/_summary.html.twig', $templateArgs);
    }

    /**
     * @Method("POST")
     * @Route("/job-seeker/resume/{id}/edit/key-qualifications/create", name="job_seeker_edit_key_qualifications_create")
     */
    public function createKeyQualifications(Request $request, Resume $resume) {
        $this->denyAccessUnlessGranted('edit', $resume);
        $form = $this->createAjaxForm($resume, KeyQualificationsType::class, 'job_seeker_edit_key_qualifications_create');
        return $this->processAjaxForm($request, $resume, $form, 'job_seeker/resume/_key_qualifications_form.html.twig', 'job_seeker/resume/_key_qualifications.html.twig', 'key-qualifications');
    }

    /**
     * @Route("/job-seeker/resume/{id}/edit/key-qualifications", name="job_seeker_edit_resume_key_qualifications")
     */
    public function keyQualifications(Request $request, Resume $resume, Bool $isEditable) {
        $templateArgs = [
            'resume' => $resume,
        ];

        if ($this->isGranted('edit', $resume) && $isEditable) {
            $form = $this->createAjaxForm($resume, KeyQualificationsType::class, 'job_seeker_edit_key_qualifications_create');
            $templateArgs = array_merge($templateArgs, [
                'form_name' => $form->getName(),
                'form' => $form->createView(),
                'section' => ['complete' => $resume->isSectionComplete('key-qualifications')]
            ]);
        }

        return $this->render('job_seeker/resume/_key_qualifications.html.twig', $templateArgs);
    }

    /**
     * @Method("POST")
     * @Route("job-seeker/resume/{id}/edit/work-history/create", name="job_seeker_edit_work_history_create")
     */
    public function createWorkHistory(Request $request, Resume $resume) {
        $this->denyAccessUnlessGranted('edit', $resume);
        $form = $this->createAjaxForm($resume, WorkHistoriesType::class, "job_seeker_edit_work_history_create");
        return $this->processAjaxForm($request, $resume, $form, 'job_seeker/resume/_work_history_form.html.twig', 'job_seeker/resume/_work_history.html.twig');
    }

    /**
     * @Route("/job-seeker/resume/{id}/edit/work-history", name="job_seeker_edit_resume_work_history")
     */
    public function workHistory(Request $request, Resume $resume, Bool $isEditable) {
        $templateArgs = [
            'resume' => $resume,
        ];

        if ($this->isGranted('edit', $resume) && $isEditable) {
            $form = $this->createAjaxForm($resume, WorkHistoriesType::class, 'job_seeker_edit_work_history_create');
            $templateArgs = array_merge($templateArgs, [
                'form_name' => $form->getName(),
                'form' => $form->createView(),
            ]);
        }

        return $this->render('job_seeker/resume/_work_history.html.twig', $templateArgs);
    }

    /**
     * @Method("POST")
     * @Route("job-seeker/resume/{id}/edit/settings/create", name="job_seeker_edit_resume_settings_create")
     */
    public function createSettings(Request $request, Resume $resume) {
        $this->denyAccessUnlessGranted('edit', $resume);
        $form = $this->createAjaxForm($resume, SettingsType::class, "job_seeker_edit_resume_settings_create");
        return $this->processAjaxForm($request, $resume, $form, 'job_seeker/resume/_settings_form.html.twig', 'job_seeker/resume/_settings.html.twig', 'settings');
    }

    /**
     * @Route("/job-seeker/resume/{id}/edit/settings", name="job_seeker_edit_resume_settings")
     */
    public function settings(Request $request, Resume $resume, Bool $isEditable) {
        $templateArgs = [
            'resume' => $resume,
        ];

        if ($this->isGranted('edit', $resume) && $isEditable) {
            $form = $this->createAjaxForm($resume, SettingsType::class, 'job_seeker_edit_resume_settings_create');
            $templateArgs = array_merge($templateArgs, [
                'form_name' => $form->getName(),
                'form' => $form->createView(),
                'section' => ['complete' => $resume->isSectionComplete('settings')]
            ]);
        }

        return $this->render('job_seeker/resume/_settings.html.twig', $templateArgs);
    }

    /**
     * @Method("POST")
     * @Route("/job-seeker/resume/{id}/edit/job-seeker/create", name="job_seeker_edit_resume_job_seeker_create")
     */
    public function createJobSeeker(Request $request, Resume $resume) {
        $this->denyAccessUnlessGranted('edit', $resume);
        $form = $this->createAjaxForm($resume, ResumeJobSeekerType::class, "job_seeker_edit_resume_job_seeker_create");
        return $this->processAjaxForm($request, $resume, $form, 'job_seeker/resume/_job_seeker_form.html.twig', 'job_seeker/resume/_job_seeker.html.twig', 'contact');
    }

    /**
     * @Route("/job-seeker/resume/{id}/edit/job-seeker", name="job_seeker_edit_resume_job_seeker")
     * @param Request $request
     * @param Resume $resume
     * @param bool $isEditable
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function jobSeeker(Request $request, Resume $resume, Bool $isEditable) {
        $templateArgs = [
            'resume' => $resume,
        ];

        if ($this->isGranted('edit', $resume) && $isEditable) {
            $form = $this->createAjaxForm($resume, ResumeJobSeekerType::class, 'job_seeker_edit_resume_job_seeker_create');
            $templateArgs = array_merge($templateArgs, [
                'form_name' => $form->getName(),
                'form' => $form->createView(),
                'section' => ['complete' => $resume->isSectionComplete('contact')]
            ]);
        }

        return $this->render('job_seeker/resume/_job_seeker.html.twig', $templateArgs);
    }

    /**
     * @Method("POST")
     * @Route("/job-seeker/resume/{id}/edit/interest-profile/create", name="job_seeker_edit_resume_interest_profile_create")
     * @param Request $request
     * @param Resume $resume
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createInterestProfile(Request $request, Resume $resume) {
        $this->denyAccessUnlessGranted('edit', $resume);
        $form = $this->createAjaxForm($resume, InterestProfileType::class, "job_seeker_edit_resume_interest_profile_create");
        return $this->processAjaxForm($request, $resume, $form, 'job_seeker/resume/_interest_profile_form.html.twig', 'job_seeker/resume/_interest_profile.html.twig', 'interest-profile');
    }

    /**
     * @Route("/job-seeker/resume/{id}/edit/interest-profile", name="job_seeker_edit_resume_interest_profile")
     * @param Request $request
     * @param Resume $resume
     * @param bool $isEditable
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function interestProfile(Request $request, Resume $resume, Bool $isEditable) {
        $templateArgs = [
            'resume' => $resume,
        ];

        if ($this->isGranted('edit', $resume) && $isEditable) {
            $form = $this->createAjaxForm($resume, InterestProfileType::class, 'job_seeker_edit_resume_interest_profile_create');
            $templateArgs = array_merge($templateArgs, [
                'form_name' => $form->getName(),
                'form' => $form->createView(),
                'section' => ['complete' => $resume->isSectionComplete('interest-profile')]
            ]);
        }

        return $this->render('job_seeker/resume/_interest_profile.html.twig', $templateArgs);
    }

    public function createAjaxForm(Resume $resume, string $type, string $route) {
        $form = $this->createForm($type, $resume, [
            'action'  => $this->generateUrl($route, ['id' => $resume->getId()]),
            'method' => 'POST',
        ]);
        return $form;
    }

    public function processAjaxForm(Request $request, Resume $resume, $form, $errorView, $successView, $section = null) {
        $this->denyAccessUnlessGranted('edit', $resume);

        /** @var JobSeekerUser $user */
        $user = $this->getUser();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            if ($section == 'contact') {
                $city = $resume->getCity();
                if ($city) {
                    $resume->setState($city->getState());
                }
            }
            $resume->setInverseSide();
            $isComplete = $resume->checkIsComplete();
            $resume->setIsComplete($isComplete);
            $em->persist($resume);
            $em->flush();

            if (!$request->isXmlHttpRequest()) {
                return $this->redirectToRoute('job_seeker_edit_resume', ['id' => $resume->getId()]);
            }

            return  new JsonResponse(
                [
                    'message' => 'Success! ' . ucwords(str_replace("_", " ", $form->getName())) . " has been updated.",
                    'display' => $this->renderView($successView, [
                        'form_name' => $form->getName(),
                        'resume' => $resume,
                        'user' => $user,
                        'form' => $form->createView(),
                        'section' => ['complete' => $resume->isSectionComplete($section)]
                    ])
                ], 200
            );
        }

        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('job_seeker_edit_resume', ['id' => $resume->getId()]);
        }

        $response =  new JsonResponse(
            [
                'message' => 'Error! Updating ' . ucwords(str_replace("_", " ", $form->getName())) . ' Please correct the errors and try again.',
                'form' => $this->renderView($errorView, [
                        'resume' => $resume,
                        'form' => $form->createView(),
                        'section' => ['complete' => $resume->isSectionComplete($section)]
                    ]
                )
            ], 400
        );

        return $response;
    }

}
