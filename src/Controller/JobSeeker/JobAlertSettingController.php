<?php

namespace App\Controller\JobSeeker;

use App\Entity\User\JobSeekerUser;
use App\Form\JobSeeker\JobAlert\JobAlertSettingType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;

class JobAlertSettingController extends AbstractController
{

    /**
     * @Route("/job-seeker/job-alert", name="job_seeker_job_alert")
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function jobAlertSettings(Request $request, TranslatorInterface $translator)
    {
        $this->denyAccessUnlessGranted('ROLE_JOBSEEKER');

        $em = $this->getDoctrine()->getManager();

        /** @var JobSeekerUser $jobSeekerUser */
        $jobSeekerUser = $this->getUser();
        $form = $this->createForm(JobAlertSettingType::class, $jobSeekerUser, [
            'action' => $this->generateUrl('job_seeker_job_alert')
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $receiveAlertsForSubmittedInterest                        = $form->get('receiveAlertsForSubmittedInterest')->getData();
            $receiveAlertsForJobsMatchingSavedSearchCriteria          = $form->get('receiveAlertsForJobsMatchingSavedSearchCriteria')->getData();
            $notificationPreferenceForSubmittedInterest               = $form->get('notificationPreferenceForSubmittedInterest')->getData();
            $notificationPreferenceForJobsMatchingSavedSearchCriteria = $form->get('notificationPreferenceForJobsMatchingSavedSearchCriteria')->getData();

            if ($receiveAlertsForSubmittedInterest != null ) {
                $jobSeekerUser->setReceiveAlertsForSubmittedInterest($receiveAlertsForSubmittedInterest);
            }

            if ($receiveAlertsForJobsMatchingSavedSearchCriteria != null) {
                $jobSeekerUser->setReceiveAlertsForJobsMatchingSavedSearchCriteria($receiveAlertsForJobsMatchingSavedSearchCriteria);
            }

            $jobSeekerUser->setNotificationPreferenceForSubmittedInterest($notificationPreferenceForSubmittedInterest);
            $jobSeekerUser->setNotificationPreferenceForJobsMatchingSavedSearchCriteria($notificationPreferenceForJobsMatchingSavedSearchCriteria);

            $em->persist($jobSeekerUser);
            $em->flush();
            $this->addFlash('success', $translator->trans('job_seeker.job_alert.job_alert_setting'));

            return $this->redirectToRoute('job_seeker_dashboard');
        }

        return $this->render('job_seeker/dashboard/_job_alerts_setting.html.twig', [
            'form'  => $form->createView()
        ]);
    }
}