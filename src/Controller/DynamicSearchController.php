<?php

namespace App\Controller;

use App\Entity\City;
use App\Entity\JobTitle\Lookup\JobTitleName;
use App\Repository\City\DepartmentRepository;
use App\Repository\City\JobTitleRepository;
use App\Repository\JobTitle\Lookup\JobTitleNameRepository;
use Doctrine\ORM\Query;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class DynamicSearchController extends AbstractController
{

    /**
     * @Route("/search/filter")
     * @param Request $request
     * @return JsonResponse
     * @throws \Doctrine\ORM\ORMException
     */
    public function searchFilter(Request $request)
    {
        $em            = $this->getDoctrine()->getManager();
        $responseData  = [];
        $jobTitleNames = [];

        // Get State
        //$responseData['state'] = [];

        // Get Counties
        $responseData['counties'] = [];
        $stateId = $request->query->get('state');

        $isLinkSearch = $request->query->get('isLinkSearch');
        $isLinkSearch = ($isLinkSearch == "true") ? true : false;

        $cities = [];
        if ($stateId) {
            $counties = $em->getRepository(City\County::class)->findActiveCountiesByState($stateId, $isLinkSearch);

            foreach ($counties as $county) {
                $responseData['counties'][] = [
                    'id' => $county->getId(),
                    'name' => $county->getName()
                ];
            }

            $cities        = $em->getRepository(City::class)->findByState($stateId);
            $jobTitleNames = $em->getRepository(JobTitleName::class)->findByState($stateId);
        }

        // Get Cities
        $responseData['cities'] = [];
        $countyIds              = $request->query->get('counties');
        if ($countyIds) {
            if ( ! is_array($countyIds)) {
                $countyIds = [$countyIds];
            }
            $cities        = $em->getRepository(City::class)->findCitiesByCounties($countyIds, $isLinkSearch);
            $jobTitleNames = $em->getRepository(JobTitleName::class)->findByCounties($countyIds);
        }

        if (count($cities)) {
            foreach ($cities as $city) {
                $responseData['cities'][] = [
                    'id' => $city->getId(),
                    'name' => $city->getName()
                ];
            }
        }

        // Get Job Titles
        $responseData['jobTitleNames'] = [];
        $cityIds = $request->query->get('cities');
        if ($cityIds) {
            $jobTitleNames = $em->getRepository(JobTitleName::class)->findByCities($cityIds);
        }

        if (count($jobTitleNames)) {
            foreach ($jobTitleNames as $jobTitleName) {
                $responseData['jobTitleNames'][] = [
                    'id' => $jobTitleName->getId(),
                    'name' => $jobTitleName->getName()
                ];
            }
        } else {
            $responseData['jobTitleNames'][] = [
                'id' => '',
                'name' => 'No Matches'
            ];
        }
        return new JsonResponse($responseData);
    }

    /**
     * @Route("/search/job-titles")
     * @param Request $request
     * @return JsonResponse
     * @throws \Doctrine\ORM\ORMException
     */
    public function searchJobTitles(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $responseData = [];

        // Get Counties
        $responseData['jobTitle'] = [];
        $department = $request->query->get('department');
        if ($department) {
            $jobTitles = $em->getRepository(City\JobTitle::class)->findJobTitlesForDepartment($department);
            if (count($jobTitles)) {
                /** @var City\JobTitle $jobTitle */
                foreach ($jobTitles as $jobTitle) {
                    $responseData['jobTitle'][] = [
                        'id' => $jobTitle->getId(),
                        'name' => $jobTitle->getJobTitleName()->getName(),
                    ];
                }
            } else {
                $responseData['jobTitle'][] = [
                    'id' => '',
                    'name' => 'No Matches'
                ];
            }
        }

        return new JsonResponse($responseData);
    }

    /**
     * @Route("/search/location", name="search_location")
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function searchLocation(Request $request)
    {
        $data      = [];
        $t         = 0;
        $pageSize  = getenv('PAGE_SIZE');
        $q         = $request->query->get('q', '');
        $page      = $request->query->get('page', 1);
        $cityRegistration = $request->query->get('city_registration');

        $repo      = $this->getDoctrine()->getManager()->getRepository(City::class);

        if ($cityRegistration) {
            $qb        = $repo->findIncorporatedCityCountyAndState($q);
        } else {
            $qb        = $repo->findCityCountyAndState($q);
        }

        $qb->setFirstResult($pageSize * ($page - 1));
        $qb->setMaxResults($pageSize);
        $items               = $qb->getResult(Query::HYDRATE_ARRAY);
        $data['per_page']    = $pageSize;
        $data['total_count'] = count($items);

        foreach ($items as $item) {
            $data['items'][$t++] = $item;
        }

        return $this->json($data);
    }

    /**
     * @Route("/search/city-address", name="search_city_address")
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function searchCityAddress(Request $request)
    {
        $data      = [];

        $cityId      = $request->query->get('city');
        if ($cityId) {
            $repo      = $this->getDoctrine()->getManager()->getRepository(City::class);
            /** @var City $city */
            $city = $repo->find($cityId);
            if ($city) {
                $data['street'] = $city->getAddress();
                $data['city'] = $city->getId();
                $data['zipcode'] = $city->getZipCode();
            }
        }

        return $this->json($data);
    }


    /**
     * @Route("/search/county", name="search_county")
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function searchCounty(Request $request)
    {
        $data      = [];
        $t         = 0;
        $pageSize  = getenv('PAGE_SIZE');
        $q         = $request->query->get('q', '');
        $page      = $request->query->get('page', 1);

        $repo      = $this->getDoctrine()->getManager()->getRepository(City\County::class);
        $qb        = $repo->findCountyAndState($q);

        $qb->setFirstResult($pageSize * ($page - 1));
        $qb->setMaxResults($pageSize);

        $items               = $qb->getResult(Query::HYDRATE_ARRAY);
        $data['results']    = [];

        foreach ($items as $item) {

            $data['results'][] = [
                'id' => $item['id'],
                'text' => $item['name'],
            ];
        }

        if (count($items) == $pageSize) {
            $data['more'] = true;
        }

        return $this->json($data);
    }

    /**
     * @Route("/search/city", name="search_city")
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function searchCity(Request $request)
    {
        $data      = [];
        $t         = 0;
        $pageSize  = getenv('PAGE_SIZE');
        $q         = $request->query->get('q', '');
        $page      = $request->query->get('page', 1);

        $repo      = $this->getDoctrine()->getManager()->getRepository(City::class);
        $qb        = $repo->findCityByTerm($q);

        $qb->setFirstResult($pageSize * ($page - 1));
        $qb->setMaxResults($pageSize);

        $data['per_page']    = $pageSize;
        $items               = $qb->getResult(Query::HYDRATE_ARRAY);
        $data['total_count'] = count($items);

        foreach ($items as $item) {
            $data['items'][$t++] = $item;
        }

        return $this->json($data);
    }

    /**
     * @Route("/search/job-title-name", name="search_job_title_name")
     * @param Request $request
     *
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function searchJobTitleName(Request $request)
    {
        $data      = [];
        $t         = 0;
        $pageSize  = $request->query->get('page_limit', getenv('PAGE_SIZE'));
        $q         = $request->query->get('q', '');
        $page      = $request->query->get('page', 1);

        /*** CIT-663 Filter JobTitle based on State, or County or City or All ***/
        $stateId   = $request->query->get('stateId');
        $countyId  = $request->query->get('countyId');

        /** @var JobTitleNameRepository $repo */
        $repo      = $this->getDoctrine()->getManager()->getRepository(JobTitleName::class);
        $qb        = $repo->findCityByTerm($q, $stateId, $countyId);

        $qb->setFirstResult($pageSize * ($page - 1));
        $qb->setMaxResults($pageSize);

        $data['results'] = [];

        $items = $qb->getResult(Query::HYDRATE_ARRAY);
        foreach ($items as $item) {
            $data['results'][$t++] = $item;
        }

        if (count($items) == $pageSize) {
            $data['more'] = true;
        }

        return $this->json($data);
    }

    /**
     * @Route("/search/department", name="search_department")
     * @param Request $request
     *
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function searchDepartment(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_SONATA_ADMIN');

        $data      = [];
        $t         = 0;
        $cityId      = $request->query->get('cityId');

        if ( ! $cityId) {
            throw new \Exception('CityId is required.');
        }

        /** @var DepartmentRepository $repo */
        $repo      = $this->getDoctrine()->getManager()->getRepository(City\Department::class);
        /* Only shows department if City is defined */

        $departments = $repo->getQueryBuilderToFindByCity($cityId)->getQuery()->getArrayResult();


        return $this->json([
            'succeed' => true,
            'departments' => $departments
        ]);
    }

}
