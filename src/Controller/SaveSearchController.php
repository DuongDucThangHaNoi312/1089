<?php

namespace App\Controller;

use App\Entity\City;
use App\Entity\City\County;
use App\Entity\City\State;
use App\Entity\JobTitle\Lookup\JobCategory;
use App\Entity\JobTitle\Lookup\JobLevel;
use App\Entity\JobTitle\Lookup\JobTitleName;
use App\Entity\JobTitle\Lookup\JobType;
use App\Entity\User\CityUser;
use App\Entity\User\JobSeekerUser;
use App\Entity\User\SavedSearch;
use App\Form\JobSeeker\Registration\JobSeekerProfileType;
use App\Form\SaveSearchType;
use App\Service\LocationGetter;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

class SaveSearchController extends AbstractController
{

    /**
     * @Route("/save-a-search", name="save_a_search")
     * @param Request $request
     * @param ValidatorInterface $validator
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function saveSearch(Request $request, ValidatorInterface $validator)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $saveSearchForm = $this->createForm(SaveSearchType::class);

        $saveSearchForm->handleRequest($request);

        if ($saveSearchForm->isSubmitted() && $saveSearchForm->isValid()) {
            $saveSearchData = $saveSearchForm->getData();
        } else {
            $this->addFlash('error', 'Save search form did not validate!');
            return $this->redirect($request->headers->get('referer'));
        }

        if ($this->getUser() instanceof JobSeekerUser) {
            /** @var JobSeekerUser $user */
            $user = $this->getUser();
            $countAllowedSavedSearches = $user->getSubscription()->getSubscriptionPlan()->getCountSavedSearches();
            // if zero or null, there is no limit
            if ($countAllowedSavedSearches) {
                $savedSearchRepo = $this->getDoctrine()->getRepository(SavedSearch::class);
                $countSavedSearches = $savedSearchRepo->count(['type' => $saveSearchData['type'], 'user' => $user, 'isDefault' => false]); // CIT-724
                if ($countSavedSearches >= $countAllowedSavedSearches) {
                    $this->addFlash('error', 'You have already saved your maximum number of searches ('.$countAllowedSavedSearches.') based upon your subscription level.');
                    return $this->redirect($request->headers->get('referer'));
                }
            }
        }

        $queryString = str_replace('GET ', '', $saveSearchData['queryString']);

        // CIT-522: should not save the _token into saved search
        $queryString = str_replace('search_filter[_token]', 'saved_search', $queryString);
        $queryString = str_replace('search_filter%5B_token%5D', 'saved_search', $queryString);

        $savedSearch = new SavedSearch();
        $savedSearch->setUser($this->getUser());
        $savedSearch->setType($saveSearchData['type']);
        $savedSearch->setSearchQuery($queryString);
        $savedSearch->setName($saveSearchData['name']);

        $errors = $validator->validate($savedSearch);
        if (count($errors)) {
            foreach ($errors as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        } else {
            $em = $this->getDoctrine()->getManager();
            $em->persist($savedSearch);
            $em->flush();

            $this->addFlash('success', 'You have saved this search.');
        }

        return $this->redirect($request->headers->get('referer'));

    }

    /**
     * @Route("/delete-saved-search/{id}", name="delete-saved-search")
     * @param SavedSearch $savedSearch
     * @param Request $request
     * @param RouterInterface $router
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteSavedSearch(SavedSearch $savedSearch, Request $request, RouterInterface $router)
    {
        $this->denyAccessUnlessGranted('delete', $savedSearch);
        $this->getDoctrine()->getManager()->remove($savedSearch);
        $this->getDoctrine()->getManager()->flush();
        $this->addFlash('success', 'Your saved search was deleted.');

        if ($this->getUser() instanceof JobSeekerUser) {
            $defaultRoute = 'job_seeker_dashboard';
        } elseif ($this->getUser() instanceof CityUser) {
            $defaultRoute = 'city_user_dashboard';
        } else {
            $defaultRoute = 'home';
        }
        $redirectUrl = $request->headers->get('referer') ? $request->headers->get('referer') : $router->generate($defaultRoute);
        return $this->redirect($redirectUrl);
    }

    /**
     * @Route("/view-saved-search-job-seeker", name="view-saved-search-job-seeker")
     * @param Request $request
     * @param RouterInterface $router
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function drawerFormSavedSearchJobSeeker(Request $request, RouterInterface $router, LocationGetter $locationGetter)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        /** @var JobSeekerUser $user */
        $user                 = $this->getUser();

        $form = $this->createForm(JobSeekerProfileType::class, $user, [
            'action'  => $this->generateUrl('view-saved-search-job-seeker'),
            'step2'   => false,
            'validation_groups' => 'Default'
        ]);

        $form->remove('save');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $state     = '';

            $counties      = $form->get('interestedCounties')->getData();
            $jobType       = $form->get('interestedJobType')->getData();
            $jobLevels     = $form->get('interestedJobLevels')->getData();

            $worksForCity    = $form->get('worksForCity')->getData();
            $currentJobTitle = $form->get('currentJobTitle')->getData();

            $jobCategories = [];
            if ($form->get('interestedJobCategoryGenerals')->getData()) {
                $jobCategories[] = $form->get('interestedJobCategoryGenerals')->getData();
            }
            if ($form->get('interestedJobCategoryNotGenerals')->getData()) {
                $jobCategories[] = $form->get('interestedJobCategoryNotGenerals')->getData();
            }
            $jobTitles     = $form->get('interestedJobTitleNames')->getData();

            /* UPDATE JOB SEEKER SEARCH CRITERIA */

            /** @var JobSeekerUser $jobSeekerUser */
            $user = $form->getData();

            // save jobTitles
            $jobTitlesNameRepo = $this->getDoctrine()->getRepository(JobTitleName::class);
            $jobCategoryRepo   = $this->getDoctrine()->getRepository(JobCategory::class);

            $user->setInterestedJobTitleNames(new ArrayCollection());

            // save jobTitleNames
            if ($jobTitles && count($jobTitles)) {
                foreach ($jobTitles as $jtnId) {
                    $jtn = $jobTitlesNameRepo->find($jtnId);
                    $user->addInterestedJobTitleName($jtn);
                }
            }

            // save jobCategories
            $user->setInterestedJobCategories(new ArrayCollection());
            foreach ($jobCategories as $catId) {
                $cat = $jobCategoryRepo->find($catId);
                $user->addInterestedJobCategory($cat);
            }

            // save works for city
            if ($worksForCity) {
                $user->setWorkForCityGovernment(true);
                $locationGetter->setJobSeekerWorksForLocation($user, $worksForCity);
            } else {
                $user->setWorkForCityGovernment(false);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);


            /* CIT-717 Becomes a Saved Search */
            foreach ($counties as $item) {
                $state = $item->getState();break;
            }

            $queryString = '/job/search?';
            if ($state) {
                $queryString .= urlencode('search_filter[state]') . '=' . $state->getId() . '&';
            }

            if ($counties && count($counties) > 0) {
                for ($i = 0; $i < count($counties); $i++) {
                    $queryString .= urlencode('search_filter[counties][]') . '=' . $counties[$i]->getId() . '&';
                }
            }

            if ($jobTitles) {
                foreach ($jobTitles as $key => $value) {
                    $queryString .= urlencode('search_filter[jobTitleNames][]') . '=' . $value . '&';
                }
            }

            $queryString .= urlencode('search_filter[user]') . '=' . $user->getId() . '&';

            if ($jobLevels && count($jobLevels) > 0) {
                foreach ($jobLevels as $level) {
                    $queryString .= urlencode('search_filter[jobLevels][]') . '=' . $level->getId() . '&';
                }
            }

            if ($jobType) {
                 $queryString .= urlencode('search_filter[jobTypes][]') . '=' . $jobType->getId() . '&';
            }

            if ($jobCategories) {
                foreach ($jobCategories as $category) {
                    $queryString .= urlencode('search_filter[jobCategories][]') . '=' . $category . '&';
                }
            }

            if ($worksForCity) {
                $queryString .= urlencode('search_filter[worksForCity]') . '=' . explode('_', $worksForCity)['0'] . '&';
            }

            if ($currentJobTitle) {
                $queryString .= urlencode('search_filter[currentJobTitle]') . '=' . urlencode($currentJobTitle);
            }

            $savedSearchRepo      = $this->getDoctrine()->getRepository(SavedSearch::class);
            $savedSearch          = $savedSearchRepo->findOneBy(['user' => $user, 'isDefault' => true]);

            $savedSearch->setSearchQuery($queryString);
            $em->persist($savedSearch);
            $em->flush();

            $this->addFlash('success', 'Default Search Criteria updated successfully.');
            return $this->redirect($queryString);
        }
        elseif ($form->isSubmitted() && !$form->isValid()) {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }
            return $this->redirect($request->headers->get('referer'));
        }


        return $this->render('job/search/_drawer_saved_search.html.twig', [
            'form'           => $form->createView(),
        ]);
    }

}
