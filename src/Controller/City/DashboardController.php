<?php

namespace App\Controller\City;

use App\Entity\City\JobTitle;
use App\Entity\JobAnnouncement;
use App\Entity\User\CityUser;
use App\Entity\User\JobSeekerUser\SubmittedJobTitleInterest;
use App\Entity\User\SavedCity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    /**
     * @Route("/city/dashboard", name="city_dashboard")
     */
    public function dashboard()
    {
        $this->denyAccessUnlessGranted(
            'ROLE_CITYUSER');

        /** @var CityUser $user */
        $user = $this->getUser();
        return $this->render('city/dashboard/index.html.twig', [
            'user' => $user,
        ]);
    }


    public function jobsOfInterest() {
        $this->denyAccessUnlessGranted('ROLE_CITYUSER');

        /** @var CityUser $user */
        $user = $this->getUser();

        $jobTitleRepository = $this->getDoctrine()->getRepository(JobTitle::class);
        $jobTitles = $jobTitleRepository->findForCityUserDashboard($user->getCity());
        $submittedJobTitleInterestRepository = $this->getDoctrine()->getRepository(SubmittedJobTitleInterest::class);
        $totalInterest = $submittedJobTitleInterestRepository->getSubmittedJobTitleInterestCountByCity($user->getCity());
        $totalJobsOfInterest = $jobTitleRepository->getTotalJobTitlesAcceptingInterest($user->getCity());
        $totalYearsOfCityExperience = $submittedJobTitleInterestRepository->getSumOfSubmittedInterestCityExperienceForCity($user->getCity());

        return $this->render('city/dashboard/_jobs_of_interest.html.twig', [
            'city' => $user->getCity(),
            'jobTitles' => $jobTitles,
            'totalInterest' => $totalInterest,
            'totalJobsOfInterest' => $totalJobsOfInterest,
            'totalYearsOfCityExperience' => $totalYearsOfCityExperience
        ]);
    }

    public function jobsToPost() {
        $this->denyAccessUnlessGranted('ROLE_CITYUSER');

        /** @var CityUser $user */
        $user = $this->getUser();

        $jobAnnouncementRepository = $this->getDoctrine()->getRepository(JobAnnouncement::class);
        $jobAnnouncements = $jobAnnouncementRepository->getJobsToPostForDashboardForCity($user->getCity());
        $totalJobAnnouncements = $jobAnnouncementRepository->getTotalJobsToPost($user->getCity());

        return $this->render('city/dashboard/_jobs_to_post.html.twig', [
            'city' => $user->getCity(),
            'jobAnnouncements' => $jobAnnouncements,
            'totalJobAnnouncements' => $totalJobAnnouncements
        ]);

    }

    public function savedCityLinks() {
        $this->denyAccessUnlessGranted('ROLE_CITYUSER');

        /** @var CityUser $user */
        $user = $this->getUser();

        $savedCityRepository = $this->getDoctrine()->getRepository(SavedCity::class);
        $savedCities = $savedCityRepository->findByUser($user, 4);
        $totalSavedCities = $savedCityRepository->getTotalSavedLinksByUser($user);

        return $this->render('city/dashboard/_saved_city_links.html.twig', [
            'city' => $user->getCity(),
            'savedCities' => $savedCities,
            'totalSavedCities' => $totalSavedCities,
        ]);
    }

    public function activeJobAnnouncements() {
        $this->denyAccessUnlessGranted('ROLE_CITYUSER');

        /** @var CityUser $user */
        $user = $this->getUser();

        $jobAnnouncementRepository = $this->getDoctrine()->getRepository(JobAnnouncement::class);
        $jobAnnouncements = $jobAnnouncementRepository->getActiveJobAnnouncementsForDashboard($user->getCity());
        $totalJobAnnouncements = $jobAnnouncementRepository->getTotalActiveJobAnnouncements($user->getCity());

        return $this->render('city/dashboard/_active_job_announcements.html.twig', [
            'city' => $user->getCity(),
            'jobAnnouncements' => $jobAnnouncements,
            'totalJobAnnouncements' => $totalJobAnnouncements
        ]);

    }

    public function endedJobAnnouncements() {
        $this->denyAccessUnlessGranted('ROLE_CITYUSER');

        /** @var CityUser $user */
        $user = $this->getUser();

        $jobAnnouncementRepository = $this->getDoctrine()->getRepository(JobAnnouncement::class);
        $jobAnnouncements = $jobAnnouncementRepository->getEndedJobAnnouncementsForDashboard($user->getCity());
        $totalJobAnnouncements = $jobAnnouncementRepository->getTotalEndedJobAnnouncements($user->getCity());

        return $this->render('city/dashboard/_ended_job_announcements.html.twig', [
            'city' => $user->getCity(),
            'jobAnnouncements' => $jobAnnouncements,
            'totalJobAnnouncements' => $totalJobAnnouncements
        ]);
    }

    public function savedResumes() {
        $this->denyAccessUnlessGranted('ROLE_CITYUSER');

        /** @var CityUser $user */
        $user = $this->getUser();

        $savedResumeRepository = $this->getDoctrine()->getRepository(CityUser\SavedResume::class);
        $savedResumes = $savedResumeRepository->getSavedResumesForDashboardForUser($user);
        $totalSavedResumes = $savedResumeRepository->getTotalSavedResumesForUser($user);

        return $this->render('city/dashboard/_saved_resumes.html.twig', [
            'city' => $user->getCity(),
            'savedResumes' => $savedResumes,
            'totalSavedResumes' => $totalSavedResumes,
        ]);
    }

    public function subscription() {
        $this->denyAccessUnlessGranted('ROLE_CITYUSER');

        /** @var CityUser $user */
        $user = $this->getUser();

        $subscription = $user->getCity()->getSubscription();
        return $this->render('city/dashboard/_subscription.html.twig', [
            'city' => $user->getCity(),
            'subscription' => $subscription
        ]);
    }

    /**
     * @Route("/dashboard/saved/resume/{id}/remove", name="remove_saved_resume")
     * @param Request $request
     * @param CityUser\SavedResume $savedResume
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeSavedResume(Request $request, CityUser\SavedResume $savedResume) {
        $this->denyAccessUnlessGranted('edit', $savedResume);
        return $this->removeObject($savedResume, $savedResume->getCityUser()->getFullname() . ' Saved Resume', $request->headers->get('referer'));
    }

    public function removeObject($object, $name, $referer) {
        try {
            $em = $this->getDoctrine()->getManager();
            $em->remove($object);
            $em->flush();
            $this->addFlash('success', 'Success! '. $name .' has been removed successfully.');
        } catch(\Exception $exception) {
            $this->addFlash('error', 'Error! Unable to remove '. $name .'at this time, please try again.');
        }

        return $this->redirect($referer);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function gettingStarted()
    {
        $this->denyAccessUnlessGranted('ROLE_CITYUSER');

        /** @var CityUser $user */
        $user = $this->getUser();

        return $this->render('city/dashboard/_getting_started.html.twig', [
            'city'         => $user->getCity()
        ]);
    }

}
