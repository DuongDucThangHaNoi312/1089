<?php

namespace App\Controller\Job;

use App\Entity\City;
use App\Entity\City\JobTitle;
use App\Entity\JobAnnouncement;
use App\Entity\SubscriptionPlan\JobSeekerSubscriptionPlan;
use App\Entity\Url;
use App\Entity\User\JobSeekerUser;
use App\Entity\User\JobSeekerUser\SavedJobTitle;
use App\Entity\User\SavedSearch;
use App\Entity\User\JobSeekerUser\SubmittedJobTitleInterest;
use App\Form\SaveSearchType;
use App\Form\Job\SearchFilterType;
use App\Service\SavedSearchHelper;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SearchController extends AbstractController
{


    /**
     * @Route("/job/search", name="job_seeker_jobtitle_search")
     * @Route("/job/search/{stateSlug}/{countySlug}/{citySlug}",
     *     name="friendly_job_seeker_jobtitle_search",
     *     defaults={"stateSlug"=null, "countySlug"=null, "citySlug"=null})
     * @ParamConverter("state", options={"mapping"={"stateSlug"="slug"}})
     * @ParamConverter("county", options={"mapping"={"countySlug"="slug"}})
     * @ParamConverter("city", options={"mapping"={"citySlug"="slug"}})
     * @param City\State $state
     * @param City $city
     * @param City\County $county
     * @param Request $request
     * @param PaginatorInterface $jobTitlePaginator
     * @param PaginatorInterface $jobAnnouncementPaginator
     * @param RouterInterface $router
     * @param SavedSearchHelper $savedSearchHelper
     *
     * @return Response
     */
    public function jobSearch(Request $request, PaginatorInterface $jobTitlePaginator, PaginatorInterface $jobAnnouncementPaginator, RouterInterface $router, SavedSearchHelper $savedSearchHelper,
        City\State $state = null, City $city = null, City\County $county = null)
    {

        if ($this->isGranted('ROLE_PENDING_JOBSEEKER')) {
            $this->addFlash('warning', 'Please complete your Registration before searching for City Links.');
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

        if ( ! $request->get('city') && $request->query->get('city')) {
            $request->attributes->set('city', $request->query->get('city'));
        }
        $searchFilter = [];
        if ($county && $county->getState() === $state) {
            $searchFilter["counties"][] = $county->getId();
        }
        if ($city && $county && in_array($city, $county->getCities()->getValues())) {
            $searchFilter["cities"][] = $city->getId();
        }
        if ($state) {
            $searchFilter["state"] = $state->getId();
            $request->query->set('search_filter', $searchFilter);
            $request->query->set('reset', 1);
        }

        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            if (!$this->isGranted('ROLE_PENDING_JOBSEEKER')) {
                $this->denyAccessUnlessGranted('ROLE_JOBSEEKER');
            }
            return $this->doJobSeekerSearch($request, $jobTitlePaginator, $jobAnnouncementPaginator, $router, $savedSearchHelper);
        }
        return $this->doAnonymousSearch($request, $jobTitlePaginator, $jobAnnouncementPaginator, $router);

    }

    private function doAnonymousSearch(Request $request, PaginatorInterface $jobTitlePaginator, PaginatorInterface $jobAnnouncementPaginator, RouterInterface $router)
    {
        $user = $this->getUser();

        $searchFilter = $request->query->get('search_filter');
        $action       = $request->getUriForPath('/job/search');

        $filterForm = $this->createForm(SearchFilterType::class, null, [
            'action'       => $action,
            'method'       => 'GET',
            'user'         => $user,
            'reset'        => $request->get('reset'),
            'submitted'    => $searchFilter && array_key_exists('_token', $searchFilter) && $searchFilter['_token'] ? true : false,
            'city'         => $request->get('city'),
            'searchFilter' => $searchFilter
        ]);
        $filterForm->handleRequest($request);

        $showPerPage = 10;
        $searchData  = [];

        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            $searchData = $filterForm->getData();
        }

        $searchFilter = $request->query->get('search_filter');

        if (isset($searchFilter['jobTitleNames'])) {
            $searchData['jobTitleNames'] = $searchFilter['jobTitleNames'];
        }

        if (isset($searchFilter['state'])) {
            $searchData['state'] = $this->getDoctrine()->getRepository(City\State::class)->find($searchFilter['state']);
        }

        if (isset($searchFilter['counties']) && count($searchFilter['counties'])) {
            $searchData['counties'] = $searchFilter['counties'];
        }

        if ($request->get('city')) {
            $cityRepo               = $this->getDoctrine()->getRepository(City::class);
            $city                   = $cityRepo->find($request->get('city'));
            $searchData['cities'][] = $city;

            $pathInfo = $request->getPathInfo();
            if ($pathInfo == '/job/search') {
                $filterForm->get('cities')->setData($searchData['cities']);
            }

            if (empty($searchData['state'])) {
                $searchData['counties'][] = $city->getCounties()[0];
                $filterForm->get('counties')->setData($searchData['counties']);
            }

            if (empty($searchData['state'])) {
                $searchData['state'] = $city->getCounties()[0]->getState();
                $filterForm->get('state')->setData($searchData['state']);
            }
        }


        $jobTitleRepo = $this->getDoctrine()->getRepository(JobTitle::class);
        $jobTitleQuery = $jobTitleRepo->getQueryWithSearchFilterData($searchData);
        $jobTitlePaginator->setDefaultPaginatorOptions([PaginatorInterface::PAGE_PARAMETER_NAME => 'jobTitlePage']);
        $jobTitlePagination = $jobTitlePaginator->paginate(
            $jobTitleQuery,
            $request->query->getInt('jobTitlePage', 1),
            $showPerPage
        );
        $jobTitlePagination->setParam('type', 'jobTitle');

        $jobAnnouncementRepo = $this->getDoctrine()->getRepository(JobAnnouncement::class);
        $jobAnnouncementQuery = $jobAnnouncementRepo->getQueryWithSearchFilterData($searchData);
        $jobAnnouncementPaginator->setDefaultPaginatorOptions([PaginatorInterface::PAGE_PARAMETER_NAME => 'jobAnnouncementPage']);
        $jobAnnouncementPagination = $jobAnnouncementPaginator->paginate(
            $jobAnnouncementQuery,
            $request->query->getInt('jobAnnouncementPage', 1),
            $showPerPage
        );
        $jobAnnouncementPagination->setParam('type', 'announcement');


        $urlData = $this->getUrlData();
        $type = $request->query->get('type');

        return $this->render('job/search/index.html.twig', [
            'type'                         => $type,
            'jobTitlePagination'           => $jobTitlePagination,
            'jobAnnouncementPagination'    => $jobAnnouncementPagination,
            'urlData'                      => $urlData,
            'savedJobTitleIDs'             => null,
            'submittedInterestJobTitleIDs' => null,
            'filterForm'                   => $filterForm->createView(),
            'queryString'                  => $request->getQueryString(),
            'user'                         => $user,
            'blockedCities'                => null,
            'allowedLevels'                => null,
            'isFriendlyUrl'                => $request->getPathInfo() != '/job/search' ? true : false,
            'formData'                     => $filterForm->getData()
        ]);
    }


    private function doJobSeekerSearch(Request $request, PaginatorInterface $jobTitlePaginator, PaginatorInterface $jobAnnouncementPaginator, RouterInterface $router, SavedSearchHelper $savedSearchHelper)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $countyRepo            = $this->getDoctrine()->getRepository(City\County::class);
        $cityRepo              = $this->getDoctrine()->getRepository(City::class);
        $jobTitleRepo          = $this->getDoctrine()->getRepository(JobTitle::class);
        $jobAnnouncementRepo   = $this->getDoctrine()->getRepository(JobAnnouncement::class);
        $savedJobTitleRepo     = $this->getDoctrine()->getRepository(SavedJobTitle::class);
        $submittedInterestRepo = $this->getDoctrine()->getRepository(SubmittedJobTitleInterest::class);
        $savedSearchRepo       = $this->getDoctrine()->getRepository(SavedSearch::class);


        /** @var JobSeekerUser $user */
        $user         = $this->getUser();
        $searchFilter = $request->get('search_filter');
        $showPerPage  = 10;
        $searchData   = [];
        $action       = $request->getUriForPath('/job/search');


        $filterForm = $this->createForm(SearchFilterType::class, null, [
            'action'       => $action,
            'method'       => 'GET',
            'user'         => $user,
            'reset'        => $request->get('reset'),
            'submitted'    => $searchFilter && array_key_exists('_token',
                $searchFilter) && $searchFilter['_token'] ? true : false,
            'city'         => $request->get('city'),
            'searchFilter' => $searchFilter
        ]);
        $filterForm->handleRequest($request);


        $newFilterForm = $this->createForm(SearchFilterType::class, null, [
            'action'       => $action,
            'method'       => 'GET',
            'user'         => $user,
            'reset'        => $request->get('reset'),
            'submitted'    => false,
            'city'         => $request->get('city'),
            'searchFilter' => $searchFilter
        ]);


        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            $searchData = $filterForm->getData();
            $newFilterForm->setData($searchData);

            if (array_key_exists('searchSubmittedJobTitle', $searchFilter)) {
                $searchData['user'] = $user->getId();
            }

            if (isset($searchData['shouldSaveSearch']) && $searchData['shouldSaveSearch']) {
                $searchName = $searchData['shouldSaveSearch'];
                $this->saveASearch($request, $searchName);
            }

            // Save Search if any
        }
        elseif ($request->get('city')) {
            $city = $cityRepo->find($request->get('city'));
            $searchData['cities'][] = $city;
            $searchData['counties'][] = $city->getCounties()[0];
            $searchData['state'] = $city->getCounties()[0]->getState();
            $newFilterForm->get('cities')->setData($searchData['cities']);
            $newFilterForm->get('counties')->setData($searchData['counties']);
            $newFilterForm->get('state')->setData($searchData['state']);
        } elseif ($request->get('saved')) {
            $searchData['saved'] = true;
            $searchData['user'] = $user->getId();
            $newFilterForm->get('saved')->setData($searchData['saved']);
            $newFilterForm->get('user')->setData($searchData['user']);
        } elseif ($request->get('searchSubmittedJobTitle')) {
            $searchData['searchSubmittedJobTitle'] = true;
            $searchData['user'] = $user->getId();
            $newFilterForm->get('searchSubmittedJobTitle')->setData($searchData['searchSubmittedJobTitle']);
            $newFilterForm->get('user')->setData($searchData['user']);
        } elseif (false == $request->get('reset')) {
            // inital access sets search criteria to user default properties from onboarding
            $searchData['counties'] = $user->getInterestedCounties();
            $searchData['state'] = null;
            if ($user->getInterestedCounties()->count()) {
                $searchData['state'] = $user->getInterestedCounties()[0]->getState();
            }
            $searchData['jobTitleNames'] = $user->getInterestedJobTitleNames();
            $searchData['jobLevels'] = $user->getInterestedJobLevels();
            $searchData['jobTypes'] = $user->getInterestedJobType() ? [$user->getInterestedJobType()] : null;
            $searchData['jobCategories'] = $user->getInterestedJobCategories();
            $newFilterForm->get('state')->setData($searchData['state']);
            $choicesCounties = $countyRepo->findBy([
                'state'    => $searchData['state'],
                'isActive' => true,
            ], ['name' => 'asc']);
            $newFilterForm
                ->add('counties', EntityType::class, [
                    'choices' => $choicesCounties,
                    'class' => City\County::class,
                    'multiple' => true,
                    'attr' => [
                        'class' => 'js-counties select2-counties'
                    ],
                    'required' => false
                ])
            ;

            $newFilterForm->get('counties')->setData($searchData['counties']);
            $newFilterForm->get('jobTitleNames')->setData($searchData['jobTitleNames']);
            $newFilterForm->get('jobLevels')->setData($searchData['jobLevels']);
            $newFilterForm->get('jobTypes')->setData($searchData['jobTypes']);
            $newFilterForm->get('jobCategories')->setData($searchData['jobCategories']);
        }
        elseif ($request->get('reset')) {
        }

        if ($user->getWorkForCityGovernment() == true) {
            $searchData['user'] = $user->getId();
            $searchData['worksForCity'] = $user->getWorksForCity();
        }


        /*** QUERY JOB TITLE WITH PAGINATION ***/
        $jobTitleQuery = $jobTitleRepo->getQueryWithSearchFilterData($searchData);
        $jobTitlePaginator->setDefaultPaginatorOptions([PaginatorInterface::PAGE_PARAMETER_NAME => 'jobTitlePage']);
        $jobTitlePagination = $jobTitlePaginator->paginate(
            $jobTitleQuery,
            $request->query->getInt('jobTitlePage', 1),
            $showPerPage
        );
        $jobTitlePagination->setParam('type', 'jobTitle');

        /*** QUERY JOB ANNOUNCEMENT WITH PAGINATION ***/
        $searchData['isJobAnnouncement'] = true;
        $jobAnnouncementQuery = $jobAnnouncementRepo->getQueryWithSearchFilterData($searchData);
        $jobAnnouncementPaginator->setDefaultPaginatorOptions([PaginatorInterface::PAGE_PARAMETER_NAME => 'jobAnnouncementPage']);
        $jobAnnouncementPagination = $jobAnnouncementPaginator->paginate(
            $jobAnnouncementQuery,
            $request->query->getInt('jobAnnouncementPage', 1),
            $showPerPage
        );
        $jobAnnouncementPagination->setParam('type', 'announcement');


        /*** SAVE SEARCH FORM ***/
        $saveSearchForm = $this->createForm(SaveSearchType::class, null, [
            'type' => SavedSearch::JOB_SEARCH_TYPE,
            'request' => $request,
            'action' => $router->generate('save_a_search'),
            'method' => 'POST'
        ]);


        /*** CREATE DEFAULT SAVED SEARCH IF NOT YET EXISTED ***/
        $savedSearches      = $savedSearchRepo->findBy(['user' => $user, 'type' => SavedSearch::JOB_SEARCH_TYPE], ['isDefault' => 'DESC', 'name' => 'ASC']);
        $countSavedSearches = $savedSearchRepo->count(['type' => SavedSearch::JOB_SEARCH_TYPE, 'user' => $user, 'isDefault' => false]);
        $defaultSearch = false;
        foreach ($savedSearches as $savedSearch) {
            if ($savedSearch->getIsDefault()) {
                $defaultSearch = $savedSearch;
            }
        }
        if ( ! $defaultSearch && $this->isGranted('ROLE_JOB_SEEKER')) {
            $savedSearchHelper->saveDefaultSearchCriteria($user);
            return $this->redirectToRoute('job_seeker_jobtitle_search');
        }


        /*** GET OTHER DATA TO DISPLAY ***/
        $savedJobTitleIDs             = $savedJobTitleRepo->getUserSavedJobTitleIDs($user);
        $submittedInterestJobTitleIDs = $submittedInterestRepo->getUserSubmittedInterestJobTitleIDs($user);
        $blockedCities                = $this->getDoctrine()->getRepository(City::class)->getCityIdBlockedByJobSeeker($user);
        $allowedLevels                = $this->getDoctrine()->getRepository(JobSeekerSubscriptionPlan::class)->getAllowedJobLevelIdsBySubscriptionPlan($user->getSubscription()->getSubscriptionPlan());
        $urlData                      = $this->getUrlData();
        $type                         = $request->query->get('type');
        $uri                          = $request->getRequestUri();
        $findDefaultSavedSearch       = ($uri == '/job/search');
        $currentSavedSearch           = $savedSearchRepo->findLikeUri($uri, $user->getId(), $findDefaultSavedSearch);

        return $this->render('job/search/index.html.twig', [
            'type'                         => $type,
            'jobTitlePagination'           => $jobTitlePagination,
            'jobAnnouncementPagination'    => $jobAnnouncementPagination,
            'urlData'                      => $urlData,
            'savedJobTitleIDs'             => $savedJobTitleIDs,
            'submittedInterestJobTitleIDs' => $submittedInterestJobTitleIDs,
            'filterForm'                   => $newFilterForm->createView(),
            'saveSearchForm'               => $saveSearchForm->createView(),
            'savedSearches'                => $savedSearches,
            'currentSavedSearch'           => $currentSavedSearch,
            'queryString'                  => $request->getQueryString(),
            'jobSeeker'                    => $user,
            'blockedCities'                => $blockedCities,
            'allowedLevels'                => $allowedLevels,
            'isFriendlyUrl'                => $request->getPathInfo() != '/job/search' ? true : false,
            'formData'                     => $filterForm->getData(),
            'countSavedSearches'           => $countSavedSearches
        ]);
    }

    private function saveASearch($request, $name)
    {
        /** @var JobSeekerUser $user */
        $user = $this->getUser();
        $countAllowedSavedSearches = $user->getSubscription()->getSubscriptionPlan()->getCountSavedSearches();

        // if zero or null, there is no limit
        if ($countAllowedSavedSearches) {
            $savedSearchRepo = $this->getDoctrine()->getRepository(SavedSearch::class);
            $countSavedSearches = $savedSearchRepo->count(['type' => 'job', 'user' => $user, 'isDefault' => false]); // CIT-724
            if ($countSavedSearches >= $countAllowedSavedSearches) {
                $this->addFlash('error', 'You have already saved your maximum number of searches ('.$countAllowedSavedSearches.') based upon your subscription level.');
                return;
            }
        }

        $queryString = $request ? $request->getRequestUri() : '';
        $queryString = urldecode($queryString);

        // CIT-522: should not save the _token into saved search
        $queryString = str_replace('search_filter[_token]', 'saved_search', $queryString);

        // CIT-522: should not save the "shouldSaveSearch" into saved search
        $sssPos = strpos($queryString, 'search_filter[shouldSaveSearch]');
        $andPos = strpos($queryString, '&', $sssPos);
        $queryString = substr($queryString, 0, $sssPos) . substr($queryString, $andPos);

        $savedSearch = new SavedSearch();
        $savedSearch->setUser($this->getUser());
        $savedSearch->setType('job');
        $savedSearch->setSearchQuery($queryString);
        $savedSearch->setName($name);

        $em = $this->getDoctrine()->getManager();
        $em->persist($savedSearch);
        $em->flush();

        $this->addFlash('success', 'Search has been saved successfully.');
    }

    private function getUrlData()
    {
        $urlRepo = $this->getDoctrine()->getRepository(Url::class);
        $urls = $urlRepo->findForJobseekerJobTitleCard([Url::JOBDESCRIPTION_TYPE, Url::AGREEMENT_TYPE, Url::SALARY_TYPE]);

        $urlData = [];
        foreach ($urls as $url) {
            if ($url['typeId'] == Url::JOBDESCRIPTION_TYPE) {
                $urlData[$url['cityId']]['descriptionType'] = $url;
            }
            if ($url['typeId'] == Url::AGREEMENT_TYPE) {
                $urlData[$url['cityId']]['agreementType'] = $url;
            }
            if ($url['typeId'] == Url::SALARY_TYPE) {
                $urlData[$url['cityId']]['salaryType'] = $url;
            }
        }

        return $urlData;
    }

    /**
     * @Route("/job-title/{id}/save", name="save_jobtitle")
     * @ParamConverter("jobTitle", options={"mapping"={"id"="id"}})
     * @param JobTitle $jobTitle
     * @param Request $request
     * @param ValidatorInterface $validator
     * @return RedirectResponse
     */
    public function saveJobTitle(JobTitle $jobTitle, Request $request, ValidatorInterface $validator)
    {

        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var JobSeekerUser $user */
        $user = $this->getUser();

        $allowedJobLevels = $user->getSubscription()->getSubscriptionPlan()->getAllowedJobLevels();

        if (
            false == $allowedJobLevels->contains($jobTitle->getLevel())
            &&
            false == ($jobTitle->isClosedPromotional() && $user->getWorksForCity() == $jobTitle->getCity())
        ) {
            $this->addFlash('error', 'Your subscription level does not allow you to save jobs with level "' . $jobTitle->getLevel() . '"');
        } else {
            $savedJobTitle = new SavedJobTitle();
            $savedJobTitle->setJobTitle($jobTitle);
            $savedJobTitle->setJobSeekerUser($this->getUser());

            $errors = $validator->validate($savedJobTitle);
            if (count($errors)) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            } else {
                $em = $this->getDoctrine()->getManager();
                $em->persist($savedJobTitle);
                $em->flush();

                $this->addFlash('success', $jobTitle->getName().' has been saved to your account.');
            }
        }

        $redirectURL = $request->headers->get('referer');

        if (strpos($redirectURL, '/job/search') !== false) {
            if ($request->get('type')) {
                if (false == strpos($redirectURL, '?')) {
                    $redirectURL .= '?type='.$request->get('type');
                } else {
                    $redirectURL .= '&type='.$request->get('type');
                }
            }
        } else {
            $redirectURL = $this->generateUrl('job_seeker_jobtitle_search');
            $redirectURL .= '?type=jobTitle';
        }

        return $this->redirect($redirectURL);

    }

    /**
     * @Route("/job-title/{id}/submit-interest", name="submit_interest")
     * @ParamConverter("jobTitle", options={"mapping"={"id"="id"}})
     * @param JobTitle $jobTitle
     * @param Request $request
     * @param ValidatorInterface $validator
     * @return RedirectResponse
     */
    public function submitInterest(JobTitle $jobTitle, Request $request, ValidatorInterface $validator)
    {

        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var JobSeekerUser $user */
        $user = $this->getUser();

        $allowedJobLevels = $user->getSubscription()->getSubscriptionPlan()->getAllowedJobLevels();

        if (
            false == $allowedJobLevels->contains($jobTitle->getLevel())
            &&
            false == ($jobTitle->isClosedPromotional() && $user->getWorksForCity() == $jobTitle->getCity())
        ) {
            $this->addFlash('error', 'Your subscription level does not allow you to submit interest in jobs with level "' . $jobTitle->getLevel() . '"');
        } else {
            $submittedInterest = new SubmittedJobTitleInterest();
            $submittedInterest->setJobTitle($jobTitle);
            $submittedInterest->setJobSeekerUser($this->getUser());
            $errors = $validator->validate($submittedInterest);
            if (count($errors)) {
                foreach ($errors as $error) {
                    $this->addFlash('danger', $error->getMessage());
                }
            } else {
                $em = $this->getDoctrine()->getManager();
                $em->persist($submittedInterest);
                $em->flush();

                $this->addFlash('success', 'You have expressed interest in '.$jobTitle->getName().'.');
            }
        }

        $redirectURL = $request->headers->get('referer');
        if (strpos($redirectURL, '/job/search') !== false) {
            if ($request->get('type')) {
                if (false == strpos($redirectURL, '?')) {
                    $redirectURL .= '?type='.$request->get('type');
                } else {
                    $redirectURL .= '&type='.$request->get('type');
                }
            }
        } else {
            $redirectURL = $this->generateUrl('job_seeker_jobtitle_search');
            $redirectURL .= '?type=jobTitle';
        }

        return $this->redirect($redirectURL);

    }

    /**
     * @Route("/job-announcement/{id}/save", name="save_job_announcement")
     * @ParamConverter("jobAnnouncement", options={"mapping"={"id"="id"}})
     * @param JobAnnouncement $jobAnnouncement
     * @param Request $request
     * @param ValidatorInterface $validator
     * @return RedirectResponse
     */
    public function saveJobAnnouncement(JobAnnouncement $jobAnnouncement, Request $request, ValidatorInterface $validator)
    {

        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var JobSeekerUser $user */
        $user = $this->getUser();
        $savedJobAnnouncement = new JobSeekerUser\SavedJobAnnouncement();
        $savedJobAnnouncement->setJobAnnouncement($jobAnnouncement);
        $savedJobAnnouncement->setJobSeekerUser($this->getUser());

        $errors = $validator->validate($savedJobAnnouncement);
        if (count($errors)) {
            foreach ($errors as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        } else {
            $em = $this->getDoctrine()->getManager();
            $em->persist($savedJobAnnouncement);
            $em->flush();

            $this->addFlash('success', $jobAnnouncement->getJobTitle()->getName().' has been saved to your account.');
        }

        $redirectURL = $request->headers->get('referer');
        if (strpos($redirectURL, '/job/search') !== false) {
            if ($request->get('type')) {
                if (false == strpos($redirectURL, '?')) {
                    $redirectURL .= '?type='.$request->get('type');
                } else {
                    $redirectURL .= '&type='.$request->get('type');
                }
            }
        } else {
            $redirectURL = $this->generateUrl('job_seeker_jobtitle_search');
            $redirectURL .= '?type=announcement';
        }

        return $this->redirect($redirectURL);

    }

    /**
     * @Route("/job-title/{id}/remove-saved", name="jobtitle_remove_from_saved_list")
     * @param JobTitle $jobTitle
     * @param Request $request
     * @param TranslatorInterface $translator
     *
     * @return RedirectResponse
     */
    public function removeJobTitleFromSavedList(JobTitle $jobTitle, Request $request, TranslatorInterface $translator)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $em            = $this->getDoctrine()->getManager();
        $savedJobTitle = $em->getRepository(SavedJobTitle::class)->findOneBy([
            'jobTitle'      => $jobTitle->getId(),
            'jobSeekerUser' => $this->getUser()->getId(),
        ]);

        if ($savedJobTitle) {
            $em->remove($savedJobTitle);
            $em->flush();

            $this->addFlash('success', $jobTitle->getName() . $translator->trans('job_seeker.job_search.remove_from_saved_list'));
        } else {
            $this->addFlash('warning', $translator->trans('job_seeker.job_search.saved_item_not_found'));
        }

        $redirectURL = $request->headers->get('referer');
        if (strpos($redirectURL, '/job/search') !== false) {
            if ($request->get('type')) {
                if (false == strpos($redirectURL, '?')) {
                    $redirectURL .= '?type='.$request->get('type');
                } else {
                    $redirectURL .= '&type='.$request->get('type');
                }
            }
        } else {
            $redirectURL = $this->generateUrl('job_seeker_jobtitle_search');
            $redirectURL .= '?type=jobTitle';
        }

        return $this->redirect($redirectURL);
    }

    /**
     * @Route("/job-title/{id}/remove-interest", name="jobtitle_remove_from_interest")
     * @param JobTitle $jobTitle
     * @param Request $request
     * @param TranslatorInterface $translator
     *
     * @return RedirectResponse
     */
    public function deleteSubmitInterest(JobTitle $jobTitle, Request $request, TranslatorInterface $translator)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $em                = $this->getDoctrine()->getManager();
        $submittedJobTitle = $em->getRepository(SubmittedJobTitleInterest::class)->findOneBy([
            'jobTitle'      => $jobTitle->getId(),
            'jobSeekerUser' => $this->getUser()->getId(),
        ]);


        if ($submittedJobTitle) {
            $em->remove($submittedJobTitle);
            $em->flush();

            $this->addFlash('success', $jobTitle->getName() . $translator->trans('job_seeker.job_search.remove_from_interest_list'));
        } else {
            $this->addFlash('warning', $translator->trans('job_seeker.job_search.submitted_item_not_found'));
        }

        $redirectURL = $request->headers->get('referer');
        if (strpos($redirectURL, '/job/search') !== false) {
            if ($request->get('type')) {
                if (false == strpos($redirectURL, '?')) {
                    $redirectURL .= '?type='.$request->get('type');
                } else {
                    $redirectURL .= '&type='.$request->get('type');
                }
            }
        } else {
            $redirectURL = $this->generateUrl('job_seeker_jobtitle_search');
            $redirectURL .= '?type=jobTitle';
        }

        return $this->redirect($redirectURL);
    }
}
