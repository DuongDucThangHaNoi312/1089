<?php

namespace App\Controller\City;

use App\Entity\JobTitle\Lookup\JobCategory;
use App\Entity\JobTitle\Lookup\JobLevel;
use App\Entity\User\CityUser;
use App\Entity\User\JobSeekerUser;
use App\Form\City\Job\Announcement\ActiveDatesType;
use App\Form\City\Job\Announcement\AlertType;
use App\Form\City\Job\Announcement\ApplicationDeadlineType;
use App\Form\City\Job\Announcement\ApplicationUrlType;
use App\Form\City\Job\Announcement\AnnouncementType;
use App\Form\City\Job\Announcement\LocationType;
use App\Form\City\Job\Announcement\RecruitmentType;
use App\Form\City\Job\Announcement\IsAlertType;
use App\Form\City\Job\Announcement\WageSalaryType;
use App\Service\JobAnnouncementStatusDecider;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\Entity\JobAnnouncement;
use App\Entity\JobAnnouncement\Lookup\JobAnnouncementStatus;
use App\Entity\City;
use Symfony\Contracts\Translation\TranslatorInterface;

class JobAnnouncementController extends AbstractController
{
    /**
     * @var JobAnnouncementStatusDecider $statusDecider
     */
    protected $statusDecider;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(JobAnnouncementStatusDecider $statusDecider, TranslatorInterface $translator)
    {
        $this->statusDecider = $statusDecider;
        $this->translator    = $translator;
    }

    /**
     * @Route("/city/{slug}/job/announcements/{id}/edit", name="city_edit_job_announcement")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     * @ParamConverter("jobAnnouncement", options={"mapping"={"id"="id"}})
     * @param City $city
     * @param JobAnnouncement $jobAnnouncement
     * @return Response
     */
    public function editAnnouncement(City $city, JobAnnouncement $jobAnnouncement)
    {
        $this->denyAccessUnlessGranted('edit', $jobAnnouncement);

        // jobAnnouncement must be from City
        if ($jobAnnouncement->getJobTitle()->getCity() !== $city) {

            throw new AccessDeniedException('Job ' . $city->getAllowsJobAnnouncements() ? 'Announcement' : 'Alert' . ' does not belong to City!');
        }
        return $this->render('city/job/announcement_edit.html.twig', [
            'jobAnnouncement' => $jobAnnouncement,
            'city' => $city
        ]);
    }

    /**
     * @Route("/city/{slug}/job/announcements/{id}/view", name="city_view_job_announcement")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     * @ParamConverter("jobAnnouncement", options={"mapping"={"id"="id"}})
     * @param City $city
     * @param JobAnnouncement $jobAnnouncement
     * @return Response
     */
    public function viewAnnouncement(City $city, JobAnnouncement $jobAnnouncement, Request $request)
    {
        /** @var JobSeekerUser $user */
        $user = $this->getUser();
        $isCityUser = false;
        $statusId   = $jobAnnouncement->getStatus()->getId();
        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            // just let them view
        }
        elseif ($this->isGranted('edit', $jobAnnouncement)) {
            $isCityUser = true;
        } elseif ($statusId !== JobAnnouncement::STATUS_ACTIVE) {
            return $this->redirectToPageNotFound($this->translator->trans('error_url_unavailable'));
        } else {
            $jobAnnouncementView = new JobAnnouncement\View($jobAnnouncement, $user);
            $jobAnnouncementView->setUserAgent($request->headers->get('User-Agent'));
            $jobAnnouncementView->setIpAddress($request->getClientIp());
            $em = $this->getDoctrine()->getManager();
            $em->persist($jobAnnouncementView);
            $em->flush();
        }

        return $this->render('city/job/announcement_view.html.twig', [
            'isCityUser' => $isCityUser,
            'jobAnnouncement' => $jobAnnouncement,
            'city' => $city
        ]);
    }

    /**
     * @Route("/city/{slug}/job/announcements/{id}/save", name="city_save_job_announcement")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     * @ParamConverter("jobAnnouncement", options={"mapping"={"id"="id"}})
     * @param Request $request
     * @param JobAnnouncement $jobAnnouncement
     * @return Response
     */
    public function saveAnnouncement(Request $request, JobAnnouncement $jobAnnouncement) {
        $this->denyAccessUnlessGranted('edit', $jobAnnouncement);

        $type = $jobAnnouncement->getIsAlert() ? 'Alert' : 'Announcement';

        if ($this->getUser() instanceof JobSeekerUser) {
            /** @var JobSeekerUser $user */
            $user = $this->getUser();
            $savedJobAnnouncement = new JobSeekerUser\SavedJobAnnouncement();
            $savedJobAnnouncement->setJobAnnouncement($jobAnnouncement);
            $savedJobAnnouncement->setJobSeekerUser($user);

            $user->addSavedJobAnnouncement($savedJobAnnouncement);

            try {
                $em = $this->getDoctrine()->getManager();
                $em->persist($savedJobAnnouncement);
                $em->persist($user);
                $em->flush();
                $message = sprintf("Error: Unable to save %s Job " . $type, $jobAnnouncement->getJobTitle()->getName());
                $this->addFlash('success', $message);
            } catch(Exception $exception) {
                $message = sprintf("Error: Unable to save %s Job ". $type .", please try again", $jobAnnouncement->getJobTitle()->getName());
                $this->addFlash('error', $message);
            }
        } else {
            $message = "Error: Only Job Seeker Users can save Job " . $type . "s";
            $this->addFlash('error', $message);
        }

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/city/{slug}/job/announcements/delete/{id}", name="city_delete_job_announcement")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     * @ParamConverter("jobAnnouncement", options={"mapping"={"id"="id"}})
     * @param Request $request
     * @param City $city
     * @param JobAnnouncement $jobAnnouncement
     * @return Response
     */
    public function deleteAnnouncement(Request $request, City $city, JobAnnouncement $jobAnnouncement) {
        $this->denyAccessUnlessGranted('edit', $city);

        $type = $jobAnnouncement->getIsAlert() ? 'Alert' : 'Announcement';
        // jobAnnouncement must be from City
        if ($jobAnnouncement->getJobTitle()->getCity() !== $city) {
            throw new AccessDeniedException('Job '. $type .' does not belong to City!');
        }

        $name = $jobAnnouncement->getJobTitle()->getName();

        // Only Job Announcements that are in To Do or Draft can be deleted. I think Scheduled is also a valid one to allow for deletion.
        switch ($jobAnnouncement->getStatus()->getId()) {
            case JobAnnouncement::STATUS_TODO:
            case JobAnnouncement::STATUS_DRAFT:
            case JobAnnouncement::STATUS_SCHEDULED:
                try {
                    $jobTitle = $jobAnnouncement->getJobTitle();
                    $jobTitle->setIsVacant(false);
                    $jobTitle->setMarkedVacantBy(null);
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($jobTitle);
                    $em->remove($jobAnnouncement);
                    $em->flush();
                    $message = sprintf("Success: %s Job ".$type." has been deleted", $name);
                    $this->addFlash('success', $message);
                }catch (Exception $exception) {
                    $message = sprintf("Error: %s Job ".$type." could not be deleted", $name);
                    $this->addFlash('error', $message);
                }
                break;
            default:
                $message = sprintf("You cannot delete %s Job ".$type.".", $name);
                $this->addFlash('warning', $message);
                break;
        }
        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/city/{slug}/job/announcements/end/{id}", name="city_end_job_announcement")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     * @ParamConverter("jobAnnouncement", options={"mapping"={"id"="id"}})
     * @param City $city
     * @param JobAnnouncement $jobAnnouncement
     * @return Response
     */
    public function endAnnouncement(City $city, JobAnnouncement $jobAnnouncement) {
        $this->denyAccessUnlessGranted('edit', $city);

        $type = $jobAnnouncement->getIsAlert() ? 'Alert' : 'Announcement';
        // Job Announcement must be from City
        if ($jobAnnouncement->getJobTitle()->getCity() !== $city) {
            throw new AccessDeniedException('Job '.$type.' does not belong to City!');
        }

        $name = $jobAnnouncement->getJobTitle()->getName();

        $args = [
            'slug' => $city,
            'status' => $jobAnnouncement->getStatus()->getSlug(),
        ];

        switch($jobAnnouncement->getStatus()->getId()) {
            case JobAnnouncement::STATUS_ACTIVE:
                try {
                    $em = $this->getDoctrine()->getManager();
                    $date = new DateTime('now');
                    $jobAnnouncement->setEndsOn($date);
                    $jobAnnouncement->setStatus($em->getReference(JobAnnouncementStatus::class, JobAnnouncement::STATUS_ENDED));
                    $em->persist($jobAnnouncement);
                    $em->flush();
                    $message = sprintf("Success: %s Job ".$type." has been ended", $name);
                    $this->addFlash('success', $message);
                } catch(Exception $exception) {
                    $message = sprintf("Error: %s Job ".$type." could not be ended", $name);
                    $this->addFlash('error', $message);
                }
                break;
            default:
                $message = sprintf("You cannot End %s Job ".$type.".", $name);
                $this->addFlash('warning', $message);
                break;
        }
        return $this->redirectToRoute('city_job_announcements', $args);
    }

    /**
     * @Route("/city/{slug}/job/announcements/archive/{id}", name="city_archive_job_announcement")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     * @ParamConverter("jobAnnouncement", options={"mapping"={"id"="id"}})
     * @param City $city
     * @param JobAnnouncement $jobAnnouncement
     * @return Response
     */
    public function archiveAnnouncement(City $city, JobAnnouncement $jobAnnouncement) {
        $this->denyAccessUnlessGranted('edit', $city);

        $type = $jobAnnouncement->getIsAlert() ? 'Alert' : 'Announcement';
        // jobAnnouncement must be from City
        if ($jobAnnouncement->getJobTitle()->getCity() !== $city) {
            throw new AccessDeniedException('Job '.$type.' does not belong to City!');
        }

        $name = $jobAnnouncement->getJobTitle()->getName();

        $args = [
            'slug' => $city,
            'status' => $jobAnnouncement->getStatus()->getSlug(),
        ];

        // Only Job Announcements that are in Active or Ended can be Archived.
        switch ($jobAnnouncement->getStatus()->getId()) {
            case JobAnnouncement::STATUS_ACTIVE:
            case JobAnnouncement::STATUS_ENDED:
                try {
                    $em = $this->getDoctrine()->getManager();
                    $jobAnnouncement->setStatus($em->getReference(JobAnnouncementStatus::class, JobAnnouncement::STATUS_ARCHIVED));
                    $jobAnnouncement->getJobTitle()->setIsVacant(false);
                    if ($this->getUser() instanceof CityUser) {
                        $jobAnnouncement->getJobTitle()->setMarkedVacantBy($this->getUser());
                    }
                    $em->persist($jobAnnouncement);
                    $em->flush();
                    $message = sprintf("Success: %s Job ".$type." has been archived", $name);
                    $this->addFlash('success', $message);
                } catch (Exception $exception) {
                    $message = sprintf("Error: %s Job ".$type." could not be archived", $name);
                    $this->addFlash('error', $message);
                }
                break;
            default:
                $message = sprintf("You cannot archive %s Job ".$type.".", $name);
                $this->addFlash('warning', $message);
                break;
        }
        return $this->redirectToRoute('city_job_announcements', $args);
    }

    /**
     * @Route("/city/{slug}/job/announcements/{status}", name="city_job_announcements")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     * @ParamConverter("jobAnnouncementStatus", options={"mapping"={"status"="slug"}})
     * @param City $city
     * @param JobAnnouncementStatus $jobAnnouncementStatus
     * @param PaginatorInterface $paginator
     * @param Request $request
     *
     * @return Response
     */
    public function announcements(City $city, JobAnnouncementStatus $jobAnnouncementStatus, PaginatorInterface $paginator, Request $request)
    {
        $this->denyAccessUnlessGranted('edit', $city);

        $filterForm = $this->createFormBuilder()
            ->add('jobTitle', TextType::class, [
                'required' => false,
                'label' => 'Filter by Job Title',
//                'attr' => ['onblur' => 'this.form.submit();']
            ])
            ->add('showPerPage', ChoiceType::class, [
                'choices' => [10 => 10, 25 => 25, 50 => 50, 100 => 100],
//                'attr' => ['onchange' => 'this.form.submit();']
            ])
            ->add('Go', SubmitType::class)
            ->setAction($request->getUri())
            ->setMethod('GET')
            ->getForm();

        $filterForm->handleRequest($request);

        $jobTitle = null;
        $showPerPage = 10;

        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            $data = $filterForm->getData();
            $jobTitle = $data['jobTitle'];
            $showPerPage = $data['showPerPage'];
        }

        $jaRepo = $this->getDoctrine()->getRepository(JobAnnouncement::class);
        $isTodoStatus = false;
        if ($jobAnnouncementStatus->getId() == JobAnnouncement::STATUS_TODO) {
            $isTodoStatus = true;
            $jobAnnouncementQuery = $jaRepo->getQueryJobAnnouncementsToPostForCity($city, $jobTitle);
        } else {
            $jobAnnouncementQuery = $jaRepo->getQueryJobAnnouncementsForCityAndStatus($city, $jobAnnouncementStatus, $jobTitle);
        }

        // in the archived job list, we will suppress the repost button if the job title already has a
        // job announcement that is not currently archived. so we get the list here and will use it in the
        // template to adjust the button behavior
        $cannotRepost = [];
        if ($jobAnnouncementStatus->getId() == JobAnnouncement::STATUS_ARCHIVED) {
            $jobTitleIDs = [];
            foreach ($jobAnnouncementQuery->getQuery()->getResult() as $ja) {
                $jobTitleIDs[$ja['job_title_id']] = true;
            }
            $cannotRepost = $jaRepo->getJobTitleIDsNotArchivedForJobTitleIds(array_keys($jobTitleIDs));
        }

        $pagination = $paginator->paginate(
            $jobAnnouncementQuery,
            $request->query->getInt('page', 1),
            $showPerPage
        );

        $canCreateNewJobAnnouncement = true;
        if (
            $city->getSubscription()->getSubscriptionPlan()->getAllowedActiveJobPostings()
            &&
            $jaRepo->getCountActiveJobAnnouncementsForCity($city) >= $city->getSubscription()->getSubscriptionPlan()->getAllowedActiveJobPostings()
        ) {
            $canCreateNewJobAnnouncement = false;
        }

        $cuRepo         = $this->getDoctrine()->getRepository(CityUser::class);
        $assignedToList = $cuRepo->findBy(['city' => $city], ['lastname' => 'ASC']);

        return $this->render('city/job/announcements.html.twig', [
            'city' => $city,
            'jobAnnouncementStatus' => $jobAnnouncementStatus,
            'isTodoStatus' => $isTodoStatus,
            'pagination' => $pagination,
            'cannotRepost' => $cannotRepost,
            'filterForm' => $filterForm->createView(),
            'canCreateNewJobAnnouncement' => $canCreateNewJobAnnouncement,
            'assignedToList' => $assignedToList
        ]);
    }

    /**
     * @Route("/city/{slug}/job/announcements/edit/{id}/status", name="city_edit_job_announcement_status")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     * @ParamConverter("jobAnnouncement", options={"mapping"={"id"="id"}})
     * @param City $city
     * @param JobAnnouncement $jobAnnouncement
     * @return Response
     */
    public function status(City $city, JobAnnouncement $jobAnnouncement) {
        $templateArgs = [
            'city' => $city,
            'jobAnnouncement' => $jobAnnouncement,
        ];

        return $this->render('city/job/announcement/_status.html.twig', $templateArgs);
    }

    /**
     * @Route("/city/{slug}/job/announcements/edit/{id}/active_dates", name="city_edit_job_announcement_active_dates")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     * @ParamConverter("jobAnnouncement", options={"mapping"={"id"="id"}})
     * @param City $city
     * @param JobAnnouncement $jobAnnouncement
     * @param bool $isEditable
     * @param bool $isFullView
     *
     * @return Response
     */
    public function activeDates(City $city, JobAnnouncement $jobAnnouncement, bool $isEditable = false, bool $isFullView = false) {
        $templateArgs = [
            'city'            => $city,
            'jobAnnouncement' => $jobAnnouncement,
            'isFullView'      => $isFullView,
        ];

        if ($this->isGranted('edit', $city) && $isEditable) {
            $form = $this->createAjaxForm($city, $jobAnnouncement, ActiveDatesType::class, 'create_city_edit_job_announcement_active_dates', ['view_timezone' => $city->getPhpTimezone()]);
            $templateArgs = array_merge($templateArgs, [
                'form_name' => $form->getName(),
                'form' => $form->createView(),
            ]);
        }

        return $this->render('city/job/announcement/_active_dates.html.twig', $templateArgs);
    }

    /**
     * @Method("POST")
     * @Route("/city/{slug}/job/announcements/edit/{id}/active_dates/create", name="create_city_edit_job_announcement_active_dates")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     * @ParamConverter("jobAnnouncement", options={"mapping"={"id"="id"}})
     * @param Request $request
     * @param City $city
     * @param JobAnnouncement $jobAnnouncement
     * @return JsonResponse|RedirectResponse
     * @throws ORMException
     */
    public function createActiveDates(Request $request, City $city, JobAnnouncement $jobAnnouncement) {
        $this->denyAccessUnlessGranted('edit', $city);
        $form = $this->createAjaxForm($city, $jobAnnouncement, ActiveDatesType::class, 'create_city_edit_job_announcement_active_dates', ['view_timezone' => $city->getPhpTimezone()]);
        return $this->processAjaxForm($request, $city, $jobAnnouncement, $form, 'city/job/announcement/_active_dates_form.html.twig', 'city/job/announcement/_active_dates.html.twig');
    }

    /**
     * @Route("/city/{slug}/job/announcements/edit/{id}/announcement", name="city_edit_job_announcement_announcement")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     * @ParamConverter("jobAnnouncement", options={"mapping"={"id"="id"}})
     * @param City $city
     * @param JobAnnouncement $jobAnnouncement
     * @param bool $isEditable
     * @return Response
     */
    public function announcement(City $city, JobAnnouncement $jobAnnouncement, bool $isEditable = false) {
        $templateArgs = [
            'city' => $city,
            'jobAnnouncement' => $jobAnnouncement,
        ];

        if ($this->isGranted('edit', $city) && $isEditable) {
            $form = $this->createAjaxForm($city, $jobAnnouncement, AnnouncementType::class, 'create_city_edit_job_announcement_announcement');
            $templateArgs = array_merge($templateArgs, [
                'form_name' => $form->getName(),
                'form' => $form->createView(),
                'section' => ['complete' => $jobAnnouncement->isSectionComplete('announcement')]
            ]);
        }

        return $this->render('city/job/announcement/_announcement.html.twig', $templateArgs);
    }

    /**
     * @Method("POST")
     * @Route("/city/{slug}/job/announcements/edit/{id}/announcement/create", name="create_city_edit_job_announcement_announcement")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     * @ParamConverter("jobAnnouncement", options={"mapping"={"id"="id"}})
     * @param Request $request
     * @param City $city
     * @param JobAnnouncement $jobAnnouncement
     * @return JsonResponse|RedirectResponse
     * @throws ORMException
     */
    public function createAnnouncement(Request $request, City $city, JobAnnouncement $jobAnnouncement) {
        $this->denyAccessUnlessGranted('edit', $city);
        $form = $this->createAjaxForm($city, $jobAnnouncement, AnnouncementType::class, 'create_city_edit_job_announcement_announcement');
        return $this->processAjaxForm($request, $city, $jobAnnouncement, $form, 'city/job/announcement/_announcement_form.html.twig', 'city/job/announcement/_announcement.html.twig', 'announcement');
    }

    /**
     * @Method("POST")
     * @Route("/city/{slug}/job/announcements/edit/{id}/alert/create", name="create_city_edit_job_announcement_alert")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     * @ParamConverter("jobAnnouncement", options={"mapping"={"id"="id"}})
     * @param Request $request
     * @param City $city
     * @param JobAnnouncement $jobAnnouncement
     * @return JsonResponse|RedirectResponse
     * @throws ORMException
     */
    public function createAlert(Request $request, City $city, JobAnnouncement $jobAnnouncement) {
        $this->denyAccessUnlessGranted('edit', $city);
        $form = $this->createAjaxForm($city, $jobAnnouncement, AlertType::class, 'create_city_edit_job_announcement_alert');
        return $this->processAjaxForm($request, $city, $jobAnnouncement, $form, 'city/job/announcement/_alert_form.html.twig', 'city/job/announcement/_alert.html.twig', 'alert');
    }


    /**
     * @Route("/city/{slug}/job/announcements/edit/{id}/is_alert", name="city_edit_job_announcement_is_alert")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     * @ParamConverter("jobAnnouncement", options={"mapping"={"id"="id"}})
     * @param City $city
     * @param JobAnnouncement $jobAnnouncement
     * @param bool $isEditable
     * @return Response
     */
    public function isAlert(City $city, JobAnnouncement $jobAnnouncement, bool $isEditable = false) {
        $templateArgs = [
            'city' => $city,
            'jobAnnouncement' => $jobAnnouncement,
        ];

        if ($this->isGranted('edit', $city) && $isEditable) {
            $form = $this->createAjaxForm($city, $jobAnnouncement, IsAlertType::class, 'create_city_edit_job_announcement_is_alert');
            $templateArgs = array_merge($templateArgs, [
                'form_name' => $form->getName(),
                'form' => $form->createView(),
                'section' => ['complete' => $jobAnnouncement->isSectionComplete('isAlert')]
            ]);
        }

        return $this->render('city/job/announcement/_is_alert.html.twig', $templateArgs);
    }

    /**
     * @Method("POST")
     * @Route("/city/{slug}/job/announcements/edit/{id}/is_alert/create", name="create_city_edit_job_announcement_is_alert")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     * @ParamConverter("jobAnnouncement", options={"mapping"={"id"="id"}})
     * @param Request $request
     * @param City $city
     * @param JobAnnouncement $jobAnnouncement
     * @return JsonResponse
     */
    public function createIsAlert(Request $request, City $city, JobAnnouncement $jobAnnouncement) {
        $this->denyAccessUnlessGranted('edit', $city);
        $em = $this->getDoctrine()->getManager();
        $postData = $request->request->get('is_alert');
        $jobAnnouncement->setIsAlert((int)$postData['isAlert']);
        $em->persist($jobAnnouncement);
        $em->flush();
        return new JsonResponse([], 200);
    }


    /**
     * @Route("/city/{slug}/job/announcements/edit/{id}/alert", name="city_edit_job_announcement_alert")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     * @ParamConverter("jobAnnouncement", options={"mapping"={"id"="id"}})
     * @param City $city
     * @param JobAnnouncement $jobAnnouncement
     * @param bool $isEditable
     * @return Response
     */
    public function alert(City $city, JobAnnouncement $jobAnnouncement, bool $isEditable = false) {
        $templateArgs = [
            'city' => $city,
            'jobAnnouncement' => $jobAnnouncement,
        ];

        if ($this->isGranted('edit', $city) && $isEditable) {
            $form = $this->createAjaxForm($city, $jobAnnouncement, AlertType::class, 'create_city_edit_job_announcement_alert');
            $templateArgs = array_merge($templateArgs, [
                'form_name' => $form->getName(),
                'form' => $form->createView(),
                'section' => ['complete' => $jobAnnouncement->isSectionComplete('alert')]
            ]);
        }

        return $this->render('city/job/announcement/_alert.html.twig', $templateArgs);
    }

    /**
     * @Route("/city/{slug}/job/announcements/edit/{id}/application_deadline", name="city_edit_job_announcement_application_deadline")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     * @ParamConverter("jobAnnouncement", options={"mapping"={"id"="id"}})
     * @param City $city
     * @param JobAnnouncement $jobAnnouncement
     * @param bool $isEditable
     * @return Response
     */
    public function applicationDeadline(City $city, JobAnnouncement $jobAnnouncement, bool $isEditable = false) {
        $templateArgs = [
            'city' => $city,
            'jobAnnouncement' => $jobAnnouncement,
        ];

        if ($this->isGranted('edit', $city) && $isEditable) {
            $form = $this->createAjaxForm($city, $jobAnnouncement, ApplicationDeadlineType::class, 'create_city_edit_job_announcement_application_deadline', ['view_timezone' => $city->getPhpTimezone()]);
            $templateArgs = array_merge($templateArgs, [
                'form_name' => $form->getName(),
                'form' => $form->createView(),
                'section' => ['complete' => $jobAnnouncement->isSectionComplete('application-deadline')]
            ]);
        }

        return $this->render('city/job/announcement/_application_deadline.html.twig', $templateArgs);
    }

    /**
     * @Method("POST")
     * @Route("/city/{slug}/job/announcements/edit/{id}/application_deadline/create", name="create_city_edit_job_announcement_application_deadline")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     * @ParamConverter("jobAnnouncement", options={"mapping"={"id"="id"}})
     * @param Request $request
     * @param City $city
     * @param JobAnnouncement $jobAnnouncement
     * @return JsonResponse|RedirectResponse
     * @throws ORMException
     */
    public function createApplicationDeadline(Request $request, City $city, JobAnnouncement $jobAnnouncement) {
        $this->denyAccessUnlessGranted('edit', $city);
        $form = $this->createAjaxForm($city, $jobAnnouncement, ApplicationDeadlineType::class, 'create_city_edit_job_announcement_application_deadline', ['view_timezone' => $city->getPhpTimezone()]);

        return $this->processAjaxForm($request, $city, $jobAnnouncement, $form, 'city/job/announcement/_application_deadline_form.html.twig', 'city/job/announcement/_application_deadline.html.twig', 'application-deadline');
    }

    /**
     * @Route("/city/{slug}/job/announcements/edit/{id}/application_url", name="city_edit_job_announcement_application_url")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     * @ParamConverter("jobAnnouncement", options={"mapping"={"id"="id"}})
     * @param City $city
     * @param JobAnnouncement $jobAnnouncement
     * @param bool $isEditable
     * @return Response
     */
    public function applicationUrl(City $city, JobAnnouncement $jobAnnouncement, bool $isEditable = false) {
        $templateArgs = [
            'city' => $city,
            'jobAnnouncement' => $jobAnnouncement,
        ];

        if ($this->isGranted('edit', $city) && $isEditable) {
            $form = $this->createAjaxForm($city, $jobAnnouncement, ApplicationUrlType::class, 'create_city_edit_job_announcement_application_url');
            $templateArgs = array_merge($templateArgs, [
                'form_name' => $form->getName(),
                'form' => $form->createView(),
            ]);
        }

        return $this->render('city/job/announcement/_application_url.html.twig', $templateArgs);
    }

    /**
     * @Method("POST")
     * @Route("/city/{slug}/job/announcements/edit/{id}/application_url/create", name="create_city_edit_job_announcement_application_url")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     * @ParamConverter("jobAnnouncement", options={"mapping"={"id"="id"}})
     * @param Request $request
     * @param City $city
     * @param JobAnnouncement $jobAnnouncement
     * @return JsonResponse|RedirectResponse
     * @throws ORMException
     */
    public function createApplicationUrl(Request $request, City $city, JobAnnouncement $jobAnnouncement) {
        $this->denyAccessUnlessGranted('edit', $city);
        $form = $this->createAjaxForm($city, $jobAnnouncement, ApplicationUrlType::class, 'create_city_edit_job_announcement_application_url');
        return $this->processAjaxForm($request, $city, $jobAnnouncement, $form, 'city/job/announcement/_application_url_form.html.twig', 'city/job/announcement/_application_url.html.twig');
    }


    /**
     * @Route("/city/{slug}/job/announcements/edit/{id}/closed_promotional", name="city_edit_job_announcement_closed_promotional")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     * @ParamConverter("jobAnnouncement", options={"mapping"={"id"="id"}})
     * @param City $city
     * @param JobAnnouncement $jobAnnouncement
     * @param bool $isEditable
     * @return Response
     */
    public function closedPromotional(City $city, JobAnnouncement $jobAnnouncement, bool $isEditable = false) {
        $templateArgs = [
            'city' => $city,
            'jobAnnouncement' => $jobAnnouncement,
        ];

        if ($this->isGranted('edit', $city) && $isEditable) {
            $form = $this->createAjaxForm($city, $jobAnnouncement, RecruitmentType::class, 'create_city_edit_job_announcement_closed_promotional');
            $templateArgs = array_merge($templateArgs, [
                'form_name' => $form->getName(),
                'form' => $form->createView(),
                'section' => ['complete' => $jobAnnouncement->isSectionComplete('closed-promotional')]
            ]);
        }

        return $this->render('city/job/announcement/_closed_promotional.html.twig', $templateArgs);
    }

    /**
     * @Method("POST")
     * @Route("/city/{slug}/job/announcements/edit/{id}/location/create", name="create_city_edit_job_announcement_location")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     * @ParamConverter("jobAnnouncement", options={"mapping"={"id"="id"}})
     * @param Request $request
     * @param City $city
     * @param JobAnnouncement $jobAnnouncement
     * @return JsonResponse|RedirectResponse
     * @throws ORMException
     */
    public function createLocation(Request $request, City $city, JobAnnouncement $jobAnnouncement) {
        $this->denyAccessUnlessGranted('edit', $city);
        $form = $this->createAjaxForm($city, $jobAnnouncement, LocationType::class, 'create_city_edit_job_announcement_location');
        return $this->processAjaxForm($request, $city, $jobAnnouncement, $form, 'city/job/announcement/_location_form.html.twig', 'city/job/announcement/_location.html.twig', 'location');
    }

    /**
     * @Route("/city/{slug}/job/announcements/edit/{id}/location", name="city_edit_job_announcement_location")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     * @ParamConverter("jobAnnouncement", options={"mapping"={"id"="id"}})
     * @param City $city
     * @param JobAnnouncement $jobAnnouncement
     * @param bool $isEditable
     * @return Response
     */
    public function location(City $city, JobAnnouncement $jobAnnouncement, bool $isEditable = false) {
        $templateArgs = [
            'city' => $city,
            'jobAnnouncement' => $jobAnnouncement,
        ];

        if ($this->isGranted('edit', $city) && $isEditable) {
            $form = $this->createAjaxForm($city, $jobAnnouncement, LocationType::class, 'create_city_edit_job_announcement_location');
            $templateArgs = array_merge($templateArgs, [
                'form_name' => $form->getName(),
                'form' => $form->createView(),
                'section' => ['complete' => $jobAnnouncement->isSectionComplete('location')]
            ]);
        }

        return $this->render('city/job/announcement/_location.html.twig', $templateArgs);
    }

    /**
     * @Method("POST")
     * @Route("/city/{slug}/job/announcements/edit/{id}/closed_promotional/create", name="create_city_edit_job_announcement_closed_promotional")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     * @ParamConverter("jobAnnouncement", options={"mapping"={"id"="id"}})
     * @param Request $request
     * @param City $city
     * @param JobAnnouncement $jobAnnouncement
     * @return JsonResponse|RedirectResponse
     * @throws ORMException
     */
    public function createClosedPromotional(Request $request, City $city, JobAnnouncement $jobAnnouncement) {
        $this->denyAccessUnlessGranted('edit', $city);
        $form = $this->createAjaxForm($city, $jobAnnouncement, RecruitmentType::class, 'create_city_edit_job_announcement_closed_promotional');
        return $this->processAjaxForm($request, $city, $jobAnnouncement, $form, 'city/job/announcement/_closed_promotional_form.html.twig', 'city/job/announcement/_closed_promotional.html.twig', 'closed-promotional');
    }

    /**
     * @Route("/city/{slug}/job/announcements/edit/{id}/wage_salary", name="city_edit_job_announcement_wage_salary")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     * @ParamConverter("jobAnnouncement", options={"mapping"={"id"="id"}})
     * @param City $city
     * @param JobAnnouncement $jobAnnouncement
     * @param bool $isEditable
     * @return Response
     */
    public function wageSalary(City $city, JobAnnouncement $jobAnnouncement, bool $isEditable = false) {
        $templateArgs = [
            'city' => $city,
            'jobAnnouncement' => $jobAnnouncement,
        ];

        if ($this->isGranted('edit', $city) && $isEditable) {
            $form = $this->createAjaxForm($city, $jobAnnouncement, WageSalaryType::class, 'create_city_edit_job_announcement_wage_salary');
            $templateArgs = array_merge($templateArgs, [
                'form_name' => $form->getName(),
                'form' => $form->createView(),
                'section' => ['complete' => $jobAnnouncement->isSectionComplete('wage-salary')]
            ]);
        }

        return $this->render('city/job/announcement/_wage_salary.html.twig', $templateArgs);
    }

    /**
     * @Method("POST")
     * @Route("/city/{slug}/job/announcements/edit/{id}/wage_salary/create", name="create_city_edit_job_announcement_wage_salary")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     * @ParamConverter("jobAnnouncement", options={"mapping"={"id"="id"}})
     * @param Request $request
     * @param City $city
     * @param JobAnnouncement $jobAnnouncement
     * @return JsonResponse|RedirectResponse
     * @throws ORMException
     */
    public function createWageSalary(Request $request, City $city, JobAnnouncement $jobAnnouncement) {
        $this->denyAccessUnlessGranted('edit', $city);
        $form = $this->createAjaxForm($city, $jobAnnouncement, WageSalaryType::class, 'create_city_edit_job_announcement_wage_salary');
        return $this->processAjaxForm($request, $city, $jobAnnouncement, $form, 'city/job/announcement/_wage_salary_form.html.twig', 'city/job/announcement/_wage_salary.html.twig', 'wage-salary');
    }

    /**
     * @Route("/city/{slug}/job/announcements/{id}/full-view", name="city_full_view_job_announcement")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     * @ParamConverter("jobAnnouncement", options={"mapping"={"id"="id"}})
     * @param City $city
     * @param JobAnnouncement $jobAnnouncement
     *
     * @param Request $request
     *
     * @return Response
     */
    public function fullViewAnnouncement(City $city, JobAnnouncement $jobAnnouncement, Request $request)
    {
        $isCityUser = false;
        $em         = $this->getDoctrine()->getManager();
        /** @var JobSeekerUser $user */
        $user       = $this->getUser();
        $isRegister = false;
        $statusId   = $jobAnnouncement->getStatus()->getId();
        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            // just let them view
        }
        elseif ($this->isGranted('edit', $jobAnnouncement)) {
            $isCityUser = true;
        } elseif ($statusId !== JobAnnouncement::STATUS_ACTIVE) {
            return $this->redirectToPageNotFound($this->translator->trans('error_url_unavailable'));
        } else {
            $jobAnnouncementView = new JobAnnouncement\View($jobAnnouncement, $user);
            if ( ! $this->checkBot()) {
                $jobAnnouncementView->setUserAgent($request->headers->get('User-Agent'));
                $jobAnnouncementView->setIpAddress($request->getClientIp());
                $em->persist($jobAnnouncementView);
                $em->flush();
            }
        }

        $referrer     = $request->server->get('HTTP_REFERER');
        $systemDomain = getenv('SYSTEM_DOMAIN');

        if ( ! $user && $referrer && strpos($referrer, $systemDomain) !== false) {
            $isRegister = true;
        }

        $searchFilter = $this->setViewSimilarJobsUrl($em, $jobAnnouncement);

        return $this->render('city/job/announcement_full_view.html.twig', [
            'jobAnnouncement' => $jobAnnouncement,
            'city'            => $city,
            'isCityUser'      => $isCityUser,
            'searchFilter'    => $searchFilter,
            'isRegister'      => $isRegister
        ]);
    }

    function checkBot()
    {
        $bots = $this->getParameter('bots');

        return (isset($_SERVER['HTTP_USER_AGENT']) && preg_match($bots, $_SERVER['HTTP_USER_AGENT']));
    }
    /**
     * @param Request $request
     * @param City $city
     * @param JobAnnouncement $jobAnnouncement
     * @param FormInterface $form
     * @param string $view
     * @param string $successView
     * @param null $section
     * @return JsonResponse|RedirectResponse
     * @throws ORMException
     */
    public function processAjaxForm(Request $request, City $city, JobAnnouncement $jobAnnouncement, FormInterface $form, string $view, string $successView, $section = null){
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            // Set the appropriate Status
            $status = $this->statusDecider->decide($jobAnnouncement);
            if ($section == 'location') {
                $city = $jobAnnouncement->getCity();
                if ($city) {
                    $jobAnnouncement->setState($city->getState());
                }
            }
            $jobAnnouncement->setStatus($status);
            $jobAnnouncement->setWageSalaryRange();
            $jobTitle = $jobAnnouncement->getJobTitle();
            switch ($status->getId()) {
                case JobAnnouncement::STATUS_ACTIVE:
                    $jobTitle->setIsVacant(true);
                    $jobTitle->setMarkedVacantBy($this->getUser());
                    break;
                default:
                    break;

            }
            $em->persist($jobTitle);
            $em->persist($jobAnnouncement);
            $em->flush();

            if (!$request->isXmlHttpRequest()) {
                return $this->redirectToRoute('city_edit_job_announcement', ['slug' => $city->getSlug(), 'id' => $jobAnnouncement->getId()]);
            }

            return new JsonResponse(
                array(
                    'message' => 'Success! '. ucwords(str_replace("_", " ", $form->getName()))   . " has been updated.",
                    'display' => $this->renderView($successView, [
                        'form_name' => $form->getName(),
                        'city' => $city,
                        'jobAnnouncement' => $jobAnnouncement,
                        'form' => $form->createView(),
                        'section' => ['complete' => $jobAnnouncement->isSectionComplete($section)]
                    ]),
                    'additional_displays' => [
                        'city-job-announcement-status' => $this->renderView('city/job/announcement/_status.html.twig', [
                            'city' => $city,
                            'jobAnnouncement' => $jobAnnouncement
                        ]),
                        'city-job-announcement-message' => $this->renderView('city/job/announcement/_message.html.twig', [
                            'city' => $city,
                            'jobAnnouncement' => $jobAnnouncement,
                            'isEditable' => true,
                        ])
                    ]
                ), 200);
        }

        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('city_edit_job_announcement', ['slug' => $city->getSlug(), 'id' => $jobAnnouncement->getId()]);
        }

        $response = new JsonResponse(
            array(
                'message' =>"<b>Error!</b> Updating " . ucwords(str_replace("_", " ", $form->getName())). ". Please correct the errors and try again.",
                'form' => $this->renderView($view,
                    array(
                        'city' => $city,
                        'jobAnnouncement' => $jobAnnouncement,
                        'form' => $form->createView(),
                        'section' => ['complete' => $jobAnnouncement->isSectionComplete($section)]
                    )),
                'additional_displays' => [
                    'city-job-announcement-status' => $this->renderView('city/job/announcement/_status.html.twig', [
                        'city' => $city,
                        'jobAnnouncement' => $jobAnnouncement
                    ]),
                    'city-job-announcement-message' => $this->renderView('city/job/announcement/_message.html.twig', [
                        'city' => $city,
                        'jobAnnouncement' => $jobAnnouncement,
                        'isEditable' => true,
                    ])
                ]
                ), 400);

        return $response;

    }

    /**
     * @param City $city
     * @param JobAnnouncement $jobAnnouncement
     * @param string $type
     * @param string $route
     * @param array $options
     * @return FormInterface
     */
    public function createAjaxForm(City $city, JobAnnouncement $jobAnnouncement, string $type, string $route, $options = []) {
        $defaultOptions = [
            'action'  => $this->generateUrl($route, ['slug' => $city->getSlug(), 'id' => $jobAnnouncement->getId()]),
            'method' => 'POST',
        ];
        $options = array_merge($defaultOptions, $options);
        $form = $this->createForm($type, $jobAnnouncement, $options);
        return $form;
    }


    /**
     * CIT-483: update the JobAnnouncement AssignedTo using an AJAX function
     *
     * @Method("POST")
     * @Route("/city/{slug}/job/announcements/{id}/update-assigned-to/{userId}", defaults={"userId"=null}, name="city_job_announcement_update_assigned_to")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     * @ParamConverter("jobAnnouncement", options={"mapping"={"id"="id"}})
     * @ParamConverter("user", options={"mapping"={"userId"="id"}})
     * @param EntityManagerInterface $em
     * @param City $city
     * @param JobAnnouncement $jobAnnouncement
     *
     * @param CityUser $user
     *
     * @return JsonResponse|RedirectResponse
     */
    public function updateJobAnnouncementAssignedTo(EntityManagerInterface $em, City $city, JobAnnouncement $jobAnnouncement, CityUser $user = null)
    {
        $this->denyAccessUnlessGranted('edit', $city);

        $jobAnnouncement->setAssignedTo($user);
        $em->flush();

        $message = $this->translator->trans('ajax.job_announcement.change_assigned_to', [
            '%jobTitle%' => $jobAnnouncement->getJobTitle(),
            '%user%'     => $user ?? 'No one'
        ]);

        return $this->json(['succeed' => true, 'message' => $message]);
    }

    /**
     * @Method("POST")
     * @Route("/count/job-announcement-view", name="count_job_announcement_view")
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function countJobAnnouncementView(Request $request)
    {
        $jobAnnouncementId = $request->get('jobAnnouncementId');
        $em                = $this->getDoctrine()->getManager();
        $jobAnnouncement   = $em->getRepository(JobAnnouncement::class)->find($jobAnnouncementId);
        $user              = $this->getUser();

        if ($jobAnnouncement) {
            $view = new JobAnnouncement\View($jobAnnouncement, $user);

            if(!$this->checkBot())
            {
                $view->setUserAgent($request->headers->get('User-Agent'));
                $view->setIpAddress($request->getClientIp());
                $em->persist($view);
                $em->flush();
            }
        }

        return $this->json(['response' => 'The view counter has been successfully updated']);
    }

    private function setViewSimilarJobsUrl($em, JobAnnouncement $jobAnnouncement, $searchFilter = []) {
        /* CIT-980 add a url "View Similar Jobs" button */
        /** @var City\State $state */
        $state                 = $em->getRepository(City\State::class)->findOneBySlug(City\State::CALIFORNIA_STATE);
        $searchFilter['state'] = $state->getId();
        $jobTitle              = $jobAnnouncement->getJobTitle();
        $categories            = $jobTitle->getCategory();
        if (count($categories)) {
            /** @var JobCategory $category */
            foreach ($categories as $category) {
                if ($category->getUsedForSimilarSearch()) {
                    $searchFilter['jobCategories'][] = $category->getId();
                }
            }
        }

        $levelRepo = $em->getRepository(JobLevel::class);
        if ($jobTitle->getLevel()) {
            if (in_array($jobTitle->getLevel()->getSlug(), [JobLevel::JOB_LEVEL_ENTRY, JobLevel::JOB_LEVEL_MID])) {
                $searchFilter['jobLevels'][] = $levelRepo->findOneBySlug(JobLevel::JOB_LEVEL_ENTRY)->getId();
                $searchFilter['jobLevels'][] = $levelRepo->findOneBySlug(JobLevel::JOB_LEVEL_MID)->getId();
            } else {
                $searchFilter['jobLevels'][] = $levelRepo->findOneBySlug(JobLevel::JOB_LEVEL_SENIOR)->getId();
                $searchFilter['jobLevels'][] = $levelRepo->findOneBySlug(JobLevel::JOB_LEVEL_EXECUTIVE)->getId();
            }
        }

        return $searchFilter;
    }

    private function redirectToPageNotFound($message)
    {
        $response = $this->render('bundles/TwigBundle/Exception/error404.html.twig', [
            'error' => $message
        ]);
        $response->setStatusCode(404);
        return $response;
    }

    public function countJobAnnouncementImpression($jobAnnouncementId, Request $request)
    {
        if ( ! $this->checkBot()) {
            $em                        = $this->getDoctrine()->getManager();
            $jobAnnouncement           = $em->getRepository(JobAnnouncement::class)->find($jobAnnouncementId);
            $jobAnnouncementImpression = new JobAnnouncement\JobAnnouncementImpression($jobAnnouncement,
                $this->getUser());
            $jobAnnouncementImpression->setUserAgent($request->headers->get('User-Agent'));
            $jobAnnouncementImpression->setIpAddress($request->getClientIp());
            $em->persist($jobAnnouncementImpression);
            $em->flush();
        }

        return new Response();
    }
}
