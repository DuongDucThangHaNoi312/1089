<?php

namespace App\Controller\JobSeeker;

use App\Entity\City;
use App\Entity\City\County;
use App\Entity\City\JobTitle;
use App\Entity\JobAnnouncement;
use App\Entity\SubscriptionPlan\JobSeekerSubscriptionPlan;
use App\Entity\User;
use App\Entity\User\JobSeekerUser;
use App\Entity\User\SavedCity;
use App\Entity\User\SavedSearch;
use App\Repository\User\JobSeekerUser\SavedJobAnnouncementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    /**
     * @Route("/job-seeker/dashboard", name="job_seeker_dashboard")
     */
    public function dashboard()
    {
        // CIT-807: Redirecting to registration flow. If they have not completed it.
        if ($this->isGranted('ROLE_PENDING_JOBSEEKER')) {
            $this->addFlash('warning', 'Please complete your Registration before accessing your dashboard');
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

        return $this->render('job_seeker/dashboard/index.html.twig', [
            'user' => $user,
        ]);
    }

    public function jobAnnouncements()
    {
        $this->denyAccessUnlessGranted('ROLE_JOBSEEKER');
        /** @var JobSeekerUser $user */
        $user = $this->getUser();

        $submittedJobTitleInterest = $user->getSubmittedJobTitleInterests();
        $savedJobAnnouncements = $user->getSavedJobAnnouncements();
        $dismissedJobAnnouncements = $user->getDismissedJobAnnouncements();

        $submittedJobTitleInterestIds = array_map(function(JobSeekerUser\SubmittedJobTitleInterest $jobTitle){
            return $jobTitle->getJobTitle()->getId();
        }, $submittedJobTitleInterest->toArray());

        $savedJobAnnouncementIds = array_map(function(JobSeekerUser\SavedJobAnnouncement $savedJobAnnouncement){
            return $savedJobAnnouncement->getJobAnnouncement()->getId();
        }, $savedJobAnnouncements->toArray());

        $dismissedJobAnnouncementIds = array_map(function(JobSeekerUser\DismissedJobAnnouncement $dismissedJobAnnouncement){
            return $dismissedJobAnnouncement->getJobAnnouncement()->getId();
        }, $dismissedJobAnnouncements->toArray());

        $jobAnnouncementRepository = $this->getDoctrine()->getRepository(JobAnnouncement::class);
        $jobAnnouncements = $jobAnnouncementRepository->findForDashboard($user, $submittedJobTitleInterestIds, $savedJobAnnouncementIds, $dismissedJobAnnouncementIds);
        $jobAnnouncementCount = $jobAnnouncementRepository->findForDashboard($user, $submittedJobTitleInterestIds, $savedJobAnnouncementIds, $dismissedJobAnnouncementIds, null, true);
        $allowedLevels = $this->getDoctrine()->getRepository(JobSeekerSubscriptionPlan::class)->getAllowedJobLevelIdsBySubscriptionPlan($user->getSubscription()->getSubscriptionPlan());

        return $this->render('job_seeker/dashboard/_job_announcements.html.twig', [
            'jobAnnouncements' => $jobAnnouncements,
            'jobAnnouncementCount' => $jobAnnouncementCount,
            'user' => $user,
            'allowedLevels' => $allowedLevels
        ]);
    }

    public function jobAlertAnnouncements()
    {
        $this->denyAccessUnlessGranted('ROLE_JOBSEEKER');
        /** @var JobSeekerUser $user */
        $user = $this->getUser();

        $jobAnnouncementRepository = $this->getDoctrine()->getRepository(JobAnnouncement::class);
        $savedSearchRepo       = $this->getDoctrine()->getRepository(SavedSearch::class);

        $jobAnnouncementCount = $jobAnnouncementRepository->getJobAnnouncementCountMatchingProfile($user, $this->getSearchData());
        $savedSearches = $savedSearchRepo->findBy(['user' => $user, 'type' => SavedSearch::JOB_SEARCH_TYPE, 'isDefault' => false], ['name' => 'ASC']);

        $countOfJobAlerts = [];
        foreach ($savedSearches as $savedSearch) {
            $searchData                              = $this->getSearchDataFromString($savedSearch->getSearchQuery());
            $countOfJobAlerts[$savedSearch->getId()] = $jobAnnouncementRepository->getCountWithSearchFilterData($searchData);
        }

        $savedJobAnnouncementRepository = $this->getDoctrine()->getRepository(JobSeekerUser\SavedJobAnnouncement::class);
        $countSavedJobAnnouncements     = $savedJobAnnouncementRepository->countByUser($user);

        return $this->render('job_seeker/dashboard/_job_alerts_announcements.html.twig', [
            'jobAnnouncementCount'      => $jobAnnouncementCount,
            'savedJobAnnouncementCount' => $countSavedJobAnnouncements,
            'user'                      => $user,
            'savedSearches'             => $savedSearches,
            'countOfJobAlerts'          => $countOfJobAlerts
        ]);
    }

    public function jobsOfInterest()
    {
        $this->denyAccessUnlessGranted('ROLE_JOBSEEKER');

        /** @var JobSeekerUser $user */
        $user = $this->getUser();

        $jobTitleRepository = $this->getDoctrine()->getRepository(JobTitle::class);
        $savedSearchRepo    = $this->getDoctrine()->getRepository(SavedSearch::class);
        $jobTitleRepo       = $this->getDoctrine()->getRepository(JobTitle::class);

        $jobTitleCount = $jobTitleRepository->getJobCountMatchingProfile($user, $this->getSearchData());
        $savedSearches = $savedSearchRepo->findBy(['user' => $user, 'type' => SavedSearch::JOB_SEARCH_TYPE, 'isDefault' => false], ['name' => 'ASC']);

        $countJobsOfInterest = [];
        foreach ($savedSearches as $savedSearch) {
            $searchData                                 = $this->getSearchDataFromString($savedSearch->getSearchQuery());
            $countJobsOfInterest[$savedSearch->getId()] = $jobTitleRepo->getCountWithSearchFilterData($searchData);
        }

        return $this->render('job_seeker/dashboard/_jobs_of_interest.html.twig', [
            'jobTitleCount'                  => $jobTitleCount,
            'submittedJobTitleInterestCount' => count($user->getSubmittedJobTitleInterests()),
            'savedJobTitleCount'             => count($user->getSavedJobTitles()),
            'user'                           => $user,
            'savedSearches'                  => $savedSearches,
            'countJobsOfInterest'            => $countJobsOfInterest
        ]);
    }

    public function savedCityLinks()
    {
        $this->denyAccessUnlessGranted('ROLE_JOBSEEKER');
        /** @var JobSeekerUser $user */
        $user = $this->getUser();

        $savedCitiesRepository = $this->getDoctrine()->getRepository(SavedCity::class);
        $savedCities = $savedCitiesRepository->findByUser($user, 4);
        $groupedBySavedCities = $this->groupBySavedCitiesByCounty($savedCities);
        return $this->render('job_seeker/dashboard/_saved_city_links.html.twig', [
            'savedCities' => $groupedBySavedCities,
            'savedCityCount' => count($savedCitiesRepository->findByUser($user, null))
        ]);
    }


    public function savedJobAnnouncements() {
        $this->denyAccessUnlessGranted('ROLE_JOBSEEKER');

        /** @var JobSeekerUser $user */
        $user = $this->getUser();

        /** @var SavedJobAnnouncementRepository $savedJobAnnouncementRepository */
        $savedJobAnnouncementRepository = $this->getDoctrine()->getRepository(JobSeekerUser\SavedJobAnnouncement::class);
        $savedJobAnnouncements = $savedJobAnnouncementRepository->findAllByUser($user);
        $countSavedJobAnnouncements = $savedJobAnnouncementRepository->countByUser($user);

        return $this->render('job_seeker/dashboard/_saved_job_announcements.html.twig', [
            'savedJobAnnouncementCount' => $countSavedJobAnnouncements,
            'savedJobAnnouncements' => $savedJobAnnouncements,
        ]);
    }

    /**
     * @param SavedCity[] $savedCities
     */
    public function groupBySavedCitiesByCounty($savedCities) {
        $result = [];

        foreach ($savedCities as $savedCity) {
            $city = $savedCity->getCity();
            foreach ($city->getCounties() as $county) {
                $result[$county->getName()][] = $savedCity;
            }
        }
        return $result;
    }


    /**
     * @return array
     */
    public function getSearchData() {
        $searchData = [];
        /** @var JobSeekerUser $user */
        $user = $this->getUser();
        $jobSeekerRepository = $this->getDoctrine()->getRepository(JobSeekerUser::class);
        if ($user->getWorkForCityGovernment() == true) {
            $searchData['user'] = $user->getId();
            $searchData['worksForCity'] = $user->getWorksForCity();
        }
        $searchData['counties'] = $user->getInterestedCounties();
        $searchData['state'] = null;
        if ($user->getInterestedCounties()->count()) {
            $searchData['state'] = $user->getInterestedCounties()[0]->getState();
        }
        $searchData['jobTitleNames'] = $user->getInterestedJobTitleNames();
        $searchData['jobLevels'] = $user->getInterestedJobLevels();
        $searchData['jobTypes'] = $user->getInterestedJobType() ? [$user->getInterestedJobType()] : null;
        $searchData['jobCategories'] = $user->getInterestedJobCategories();

        return $searchData;
    }

    /**
     * @Route("/dashboard/job/announcement/{id}/dismiss", name="dismiss_job_announcement")
     * @param Request $request
     * @param JobAnnouncement $jobAnnouncement
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function dismissJobAnnouncement(Request $request, JobAnnouncement $jobAnnouncement) {
        $this->denyAccessUnlessGranted('ROLE_JOBSEEKER');

        /** @var JobSeekerUser $user */
        $user = $this->getUser();

        $dismissedJobAnnouncement = new JobSeekerUser\DismissedJobAnnouncement();
        $dismissedJobAnnouncement->setJobSeekerUser($user);
        $dismissedJobAnnouncement->setJobAnnouncement($jobAnnouncement);

        $user->addDismissedJobAnnouncement($dismissedJobAnnouncement);

        try {
            $em = $this->getDoctrine()->getManager();
            $em->persist($dismissedJobAnnouncement);
            $em->persist($user);
            $em->flush();
            $message = sprintf('Success: Dismissed %s Job Announcement', $jobAnnouncement->getJobTitle()->getName());
            $this->addFlash('success', $message);
        } catch(\Exception $exception) {
            $message = sprintf('Error: Unable to dismiss %s Job Announcement, please try again.', $jobAnnouncement->getJobTitle()->getName());
            $this->addFlash('error', $message);
        }

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/dashboard/job/title/{id}/dismiss", name="dismiss_job_title")
     * @param Request $request
     * @param JobTitle $jobTitle
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function dismissJobTitle(Request $request, JobTitle $jobTitle) {
        $this->denyAccessUnlessGranted('ROLE_JOBSEEKER');

        /** @var JobSeekerUser $user */
        $user = $this->getUser();

        $dismissedJobTitle = new JobSeekerUser\DismissedJobTitle();
        $dismissedJobTitle->setJobSeekerUser($user);
        $dismissedJobTitle->setJobTitle($jobTitle);

        $user->addDismissedJobTitles($dismissedJobTitle);

        try {
            $em = $this->getDoctrine()->getManager();
            $em->persist($dismissedJobTitle);
            $em->persist($user);
            $em->flush();
            $message = sprintf('Success: Dismissed Job %s', $jobTitle->getName());
            $this->addFlash('success', $message);
        } catch(\Exception $exception) {
            $message = sprintf('Error: Unable to dismiss Job %s, please try again.', $jobTitle->getName());
            $this->addFlash('error', $message);
        }

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/dashboard/saved/city/{id}/remove", name="remove_saved_city")
     * @param Request $request
     * @param SavedCity $savedCity
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeSavedCityLink(Request $request, SavedCity $savedCity)
    {
        $this->denyAccessUnlessGranted('edit', $savedCity);
        return $this->removeObject($savedCity, 'Saved City', $request->headers->get('referer'));
    }

    /**
     * @Route("/dashboard/saved/job_title/{id}/remove", name="remove_saved_job_title")
     * @param Request $request
     * @param JobSeekerUser\SavedJobTitle $savedJobTitle
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeJobOfInterest(Request $request, JobSeekerUser\SavedJobTitle $savedJobTitle)
    {
        $this->denyAccessUnlessGranted('edit', $savedJobTitle);
        return $this->removeObject($savedJobTitle, 'Saved Job Title', $request->headers->get('referer'));
    }

    /**
     * @Route("/dashboard/saved/submitted_interest/{id}/remove", name="remove_submitted_interest")
     * @param Request $request
     * @param JobSeekerUser\SubmittedJobTitleInterest $submittedJobTitleInterest
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeSubmittedInterest(Request $request, JobSeekerUser\SubmittedJobTitleInterest $submittedJobTitleInterest)
    {
        $this->denyAccessUnlessGranted('edit', $submittedJobTitleInterest);
        return $this->removeObject($submittedJobTitleInterest, 'Saved Job Title Interest', $request->headers->get('referer'));
    }

    /**
     * @Route("/dashboard/saved/job_announcement/{id}/remove", name="remove_saved_job_announcement")
     * @param Request $request
     * @param JobSeekerUser\SavedJobAnnouncement $savedJobAnnouncement
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeSavedJobAnnouncement(Request $request, JobSeekerUser\SavedJobAnnouncement $savedJobAnnouncement) {
        $this->denyAccessUnlessGranted('edit', $savedJobAnnouncement);
        return $this->removeObject($savedJobAnnouncement, 'Saved Job Announcement', $request->headers->get('referer'));
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

    private function getSearchDataFromString($string)
    {
        $searchFilters = [];
        $data          = [];

        $urls = explode('?', $string);
        if (isset($urls[1])) {
            $searchFilters = explode('&', $urls[1]);
        }

        if (count($searchFilters)) {
            foreach ($searchFilters as $value) {
                $data = $this->explodeQueryString($data, $value);
            }
        }

        return $data;
    }

    private function explodeQueryString($data, $value)
    {
        $filter = ['state', 'counties', 'cities', 'jobTitleNames', 'jobLevels', 'jobTypes', 'jobCategories', 'population', 'worksForCity', 'employees', 'saved', 'searchSubmittedJobTitle', 'user'];
        $arrayItems = ['counties', 'cities', 'jobTitleNames', 'jobLevels', 'jobTypes', 'jobCategories'];

        foreach ($filter as $item) {
            if (! in_array($item, $arrayItems)) {
                if(strpos($value, $item) !== false) {
                    $data[$item] = explode('=', $value)[1];
                }
            } elseif(strpos($value, $item) !== false) {
                $data[$item][] = explode('=', $value)[1];
            }
        }

        return $data;
    }
}
