<?php

namespace App\Controller\City;

use App\Controller\SaveSearchController;
use App\Entity\City;
use App\Entity\Url;
use App\Entity\User;
use App\Entity\User\CityUser;
use App\Entity\User\JobSeekerUser;
use App\Entity\User\SavedSearch;
use App\Entity\User\SavedCity;
use App\Form\City\SearchFilterType;
use App\Form\SaveSearchType;
use App\Repository\City\CensusPopulationRepository;
use App\Repository\CityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class SearchController extends AbstractController
{

    /**
     * @var CityRepository
     */
    private $cityRepository;
    /**
     * @var CensusPopulationRepository
     */
    private $censusPopulationRepository;

    public function __construct(CityRepository $cityRepository,
                                CensusPopulationRepository $censusPopulationRepository)
    {
        $this->cityRepository = $cityRepository;
        $this->censusPopulationRepository = $censusPopulationRepository;
    }

    /**
     * @Route("/city/search", name="city_search")
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @param RouterInterface $router
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request, PaginatorInterface $paginator, RouterInterface $router)
    {
        // CIT-807: Redirecting to registration flow. If they have not completed it.
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

        /** @var User $user */
        $user = $this->getUser();
        if ($user instanceof JobSeekerUser) {
            /** @var JobSeekerUser $user */
            if ($user->getSubscription()->getSubscriptionPlan()->getLimitCityLinkSearchToCountyOfResidence()) {
                $userResidenceCounty = $user->getCounty();
                if ( ! $userResidenceCounty->getActivateForCitySearch() &&  false == $userResidenceCounty->getIsActive()) {
                    return $this->render('city/search/no-active-county.html.twig');
                }
            }

        }

        // if user is job seeker with basic subscription, and their county of residence is not an active county
        // then give them a page that prompts them to upgrade, instead of the search page.


        $filterForm = $this->createForm(SearchFilterType::class, null, [
            'action' => $request->getUri(),
            'method' => 'GET',
            'user' => $this->getUser(),
            'reset' => $request->get('reset'),
            'submitted' => isset($request->get('search_filter')['_token']) ? true : false
        ]);

        $searchFilter = $request->query->get('search_filter');

        if (isset($searchFilter['counties']) and $searchFilter['counties']) {
            $maxEmployees = [];

            foreach ($searchFilter['counties'] as $country) {
                $maxEmployees[] = $this->cityRepository->getMaxEmployees([$country]);
            }
            $employees = explode(';', $searchFilter['employees']);
            if (in_array($employees[1], $maxEmployees) || $employees[1] > max($maxEmployees)) {
                $searchFilter['employees'] = 0 . ';' . max($maxEmployees);
                $request->query->set('search_filter', $searchFilter);
            }

            $maxPopulation = [];
            foreach ($searchFilter['counties'] as $country) {
                $maxPopulation[] = $this->censusPopulationRepository->getMaxPopulation([$country]) ?? 100;
            }
            $population = explode(';', $searchFilter['population']);

            if (in_array($population[1], $maxPopulation) || $population[1] > max($maxPopulation)) {
                $searchFilter['population'] = 0 . ';' . max($maxPopulation);
                $request->query->set('search_filter', $searchFilter);
            }
        } else {
            $maxEmployees[]             = $this->cityRepository->getMaxEmployees(null);
            $maxPopulation[]            = $this->censusPopulationRepository->getMaxPopulation(null);
            $searchFilter['employees']  = 0 . ';' . max($maxEmployees);
            $searchFilter['population'] = 0 . ';' . max($maxPopulation);
            $request->query->set('search_filter', $searchFilter);
        }

        $filterForm->handleRequest($request);

        $showPerPage = 50;
        $searchData = [];

        if (isset($searchFilter['state'])) {
            $searchData['state'] = $this->getDoctrine()->getRepository(City\State::class)->find($searchFilter['state']);
        }
        if (isset($searchFilter['counties']) && count($searchFilter['counties'])) {
            $searchData['counties'] = $searchFilter['counties'];
        }

        $countyRepo = $this->getDoctrine()->getRepository(City\County::class);
        $cityRepo = $this->getDoctrine()->getRepository(City::class);
        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            $searchData = $filterForm->getData();
            if (isset($searchData['shouldSaveSearch']) && $searchData['shouldSaveSearch']) {
                $saveSearchData['name'] = $searchData['shouldSaveSearch'];
                $saveSearchData['type'] = 'city';
                $this->saveASearch($request, $saveSearchData);
            }
        } elseif ($user instanceof JobSeekerUser) {
            $filterForm = $this->createForm(SearchFilterType::class, $filterForm->getData(), [
                'action' => $request->getUri(),
                'method' => 'GET',
                'user' => $this->getUser(),
                'reset' => $request->get('reset'),
                'submitted' => false
            ]);

            // free trial users limited to profile county
            if ($user->getSubscription()->getSubscriptionPlan()->getLimitCityLinkSearchToCountyOfResidence()) {
                $searchData['counties'][] = $user->getCounty();
                $searchData['state'] = $user->getCounty()->getState();
                $choicesCounties = $countyRepo->findByCountyIDs([$user->getCounty()->getId()], true);
                $filterForm
                    ->add('counties', EntityType::class, [
                        'choices' => $choicesCounties,
                        'class' => City\County::class,
                        'multiple' => true,
                        'attr' => [
// GLR this class causes country restriction logic to be skipped                            'class' => 'js-counties'
                        ],
                        'required' => false
                    ])
                ;
                $filterForm->get('counties')->setData($searchData['counties']);
            } elseif ($request->get('saved')) {
                $searchData['saved'] = true;
                $searchData['user'] = $user->getId();
                $filterForm->get('saved')->setData($searchData['saved']);
                $filterForm->get('user')->setData($searchData['user']);
            } elseif (false == $request->get('reset')) {
                if (count($filterForm->getData()['counties']) > 0) {
                    $searchData['counties'] = $filterForm->getData()['counties'];
                } else {
                    $searchData['counties'] = $user->getInterestedCounties();
                }

                $searchData['state'] = null;
                if ($user->getInterestedCounties()->count()) {
                    $searchData['state'] = $user->getInterestedCounties()[0]->getState();
                    $choicesCounties = $countyRepo->findActiveCountiesByState($searchData['state'], true);
                    $filterForm
                        ->add('counties', EntityType::class, [
                            'choices' => $choicesCounties,
                            'class' => City\County::class,
                            'multiple' => true,
                            'attr' => [
                                'class' => 'js-counties'
                            ],
                            'required' => false
                        ])
                    ;
                }
                $filterForm->get('counties')->setData($searchData['counties']);
            }
        } elseif ($user instanceof CityUser) {
            $filterForm = $this->createForm(SearchFilterType::class, $filterForm->getData(), [
                'action' => $request->getUri(),
                'method' => 'GET',
                'user' => $this->getUser(),
                'reset' => $request->get('reset'),
                'submitted' => false
            ]);
            if ($request->get('saved')) {
                $searchData['saved'] = true;
                $searchData['user'] = $user->getId();
                $filterForm->get('saved')->setData($searchData['saved']);
                $filterForm->get('user')->setData($searchData['user']);
            } elseif (false == $request->get('reset')) {
                $em = $this->getDoctrine()->getManager();
                $userCityIDs[] = $user->getCity()->getId();
                $searchData['counties'] = $em->getRepository(City\County::class)->findByCities($userCityIDs);
                $searchData['state'] = null;
                if (count($searchData['counties'])) {
                    $searchData['state'] = $searchData['counties'][0]->getState();
                    $choicesCounties = $countyRepo->findActiveCountiesByState($searchData['state'], true);
                    $filterForm
                        ->add('counties', EntityType::class, [
                            'choices' => $choicesCounties,
                            'class' => City\County::class,
                            'multiple' => true,
                            'attr' => [
                                'class' => 'js-counties'
                            ],
                            'required' => false
                        ])
                    ;
                }
                $filterForm->get('counties')->setData($searchData['counties']);
            }
        }

        $cityQuery = $cityRepo->getQueryWithSearchFilterData($searchData);

        $pagination = $paginator->paginate(
            $cityQuery,
            $request->query->getInt('page', 1),
            $showPerPage
        );

        // the following strategy is used to reduce query counts retrieving objects related to the list of Cities
        $countyRepo = $this->getDoctrine()->getRepository(City\County::class);
        $urlRepo = $this->getDoctrine()->getRepository(Url::class);
        $savedCityRepo = $this->getDoctrine()->getRepository(SavedCity::class);

        $cityIDs = array_column($pagination->getItems(), 'id');
        $counties = $countyRepo->findForCityIDs($cityIDs, true);
        $urls = $urlRepo->findForCityIDs($cityIDs);

        $cityAddedData = [];
        foreach ($counties as $county) {
            $cityAddedData[$county['cityId']]['countyNames'][] = $county['countyName'];
            $cityAddedData[$county['cityId']]['countySlug'][] = $county['countySlug'];
            $cityAddedData[$county['cityId']]['stateName'] = $county['stateName'];
            $cityAddedData[$county['cityId']]['stateSlug'] = $county['stateSlug'];
            $cityAddedData[$county['cityId']]['otherUrls'] = [];
        }

        foreach ($urls as $url) {
            if (false == isset($cityAddedData[$url['cityId']]['firstUrl'])) {
                if ($url['typeId'] == Url::JOBSEEKER_DEFAULT_TYPE) {
                    if ($this->isGranted('ROLE_JOBSEEKER') || $this->isGranted('IS_AUTHENTICATED_ANONYMOUSLY')) {
                        $cityAddedData[$url['cityId']]['firstUrl'] = $url;
                    } else {
                        $cityAddedData[$url['cityId']]['otherUrls'][] = $url;
                    }
                } elseif ($url['typeId'] == Url::CITYUSER_DEFAULT_THPE) {
                    if ($this->isGranted('ROLE_CITYUSER')) {
                        $cityAddedData[$url['cityId']]['firstUrl'] = $url;
                    } else {
                        $cityAddedData[$url['cityId']]['otherUrls'][] = $url;
                    }
                } else {
                    $cityAddedData[$url['cityId']]['otherUrls'][] = $url;
                }
                continue;
            }

            $cityAddedData[$url['cityId']]['otherUrls'][] = $url;
        }

        $savedCityIDs = null;
        $saveSearchFormView = null;
        $savedSearches = null;
        $currentSavedSearch = null;
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            $savedCityIDs = $savedCityRepo->getUserSavedCityIDs($this->getUser());
            $saveSearchForm = $this->createForm(SaveSearchType::class, null, [
                'type' => SavedSearch::CITY_SEARCH_TYPE,
                'request' => $request,
                'action' => $router->generate('save_a_search'),
                'method' => 'POST'
            ]);
            $saveSearchFormView = $saveSearchForm->createView();

            $savedSearchRepo = $this->getDoctrine()->getRepository(SavedSearch::class);
            $savedSearches = $savedSearchRepo->findBy(['user' => $this->getUser(), 'type' => SavedSearch::CITY_SEARCH_TYPE], ['name' => 'ASC']);

            $uri = $request->getRequestUri();
            $currentSavedSearchId = array_search(substr($uri, 0, strpos($uri, "search_filter%5B_token%5D")), array_map(function(SavedSearch $savedSearch){return substr($savedSearch->getSearchQuery(), 0, strpos($savedSearch->getSearchQuery(), "saved_search"));}, $savedSearches));
            if ($currentSavedSearchId !== false) {
                $currentSavedSearch = $savedSearches[$currentSavedSearchId];
            }
        }

        return $this->render('city/search/index.html.twig', [
            'filterForm'         => $filterForm->createView(),
            'saveSearchForm'     => $saveSearchFormView,
            'savedSearches'      => $savedSearches,
            'queryString'        => $request->getQueryString(),
            'pagination'         => $pagination,
            'user'               => $this->getUser(),
            'currentSavedSearch' => $currentSavedSearch,
            'cityAddedData'      => $cityAddedData,
            'savedCityIDs'       => $savedCityIDs
        ]);
    }

    /**
     * @Route("/city/{slug}/save", name="save-city")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     * @param City $city
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function saveCity(City $city, Request $request, ValidatorInterface $validator)
    {

        $this->denyAccessUnlessGranted('ROLE_USER');

        $savedCity = new SavedCity();
        $savedCity->setCity($city);
        $savedCity->setUser($this->getUser());

        $errors = $validator->validate($savedCity);
        if (count($errors)) {
            foreach ($errors as $error) {
                $this->addFlash('danger', $error->getMessage());
            }
        } else {
            $em = $this->getDoctrine()->getManager();
            $em->persist($savedCity);
            $em->flush();

            $this->addFlash('success', $city->getName().' has been saved to your account.');
        }

        return $this->redirect($request->headers->get('referer'));

    }

    /**
     * @Route("/city/{slug}/unsave", name="unsave-city")
     * @ParamConverter("city", options={"mapping"={"slug"="slug"}})
     * @param City $city
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function unsaveCity(City $city, Request $request) {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $em = $this->getDoctrine()->getManager();

        try {
            $savedCity = $em->getRepository(SavedCity::class)->findOneBy(['city'=> $city->getId(), 'user' => $this->getUser()]);
            $em->remove($savedCity);
            $em->flush();
            $this->addFlash('success', $city->getName().' has been unsaved.');
        }catch (\Exception $e) {
            $this->addFlash('error', 'Error! Unable to unsave '. $city->getName() . ' at this time, please try again.');
        }

        return $this->redirect($request->headers->get('referer'));

    }

    private function saveASearch($request, $saveSearchData)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($this->getUser() instanceof JobSeekerUser) {
            /** @var JobSeekerUser $user */
            $user                      = $this->getUser();
            $countAllowedSavedSearches = $user->getSubscription()->getSubscriptionPlan()->getCountSavedSearches();
            if ($countAllowedSavedSearches) {
                $savedSearchRepo    = $this->getDoctrine()->getRepository(SavedSearch::class);
                $countSavedSearches = $savedSearchRepo->count([
                    'type'      => $saveSearchData['type'],
                    'user'      => $user,
                    'isDefault' => false
                ]);
                if ($countSavedSearches >= $countAllowedSavedSearches) {
                    $this->addFlash('error',
                        'You have already saved your maximum number of searches (' . $countAllowedSavedSearches . ') based upon your subscription level.');

                    return;
                }
            }
        }

        $queryString = $request ? $request->getRequestUri() : '';
        $queryString = urldecode($queryString);
        $queryString = str_replace('search_filter[_token]', 'saved_search', $queryString);
        $sssPos      = strpos($queryString, 'search_filter[shouldSaveSearch]');
        $andPos      = strpos($queryString, '&', $sssPos);
        $queryString = substr($queryString, 0, $sssPos) . substr($queryString, $andPos);

        $savedSearch = new SavedSearch();
        $savedSearch->setUser($this->getUser());
        $savedSearch->setType($saveSearchData['type']);
        $savedSearch->setSearchQuery($queryString);
        $savedSearch->setName($saveSearchData['name']);

        $em = $this->getDoctrine()->getManager();
        $em->persist($savedSearch);
        $em->flush();
        $this->addFlash('success', 'You have saved this search.');
    }

}
