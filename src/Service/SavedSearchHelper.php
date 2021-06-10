<?php

namespace App\Service;

use App\Entity\City\State;
use App\Entity\User;
use App\Entity\User\SavedSearch;
use Doctrine\ORM\EntityManagerInterface;

class SavedSearchHelper
{

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param User\JobSeekerUser $user
     */
    public function saveDefaultSearchCriteria($user)
    {
        $savedSearch = $this->em->getRepository(SavedSearch::class)->findOneBy([
            'user'      => $user->getId(),
            'isDefault' => true
        ]);

        if ( ! $savedSearch) {
            $savedSearch = new SavedSearch();
        }


        $state     = '';
        $counties  = $user->getInterestedCounties();
        $jobTitles = $user->getInterestedJobTitleNames();

        $jobLevels     = $user->getInterestedJobLevels();
        $jobType       = $user->getInterestedJobType();
        $jobCategories = $user->getInterestedJobCategories();

        $worksForCity    = $user->getWorksForCity();
        $currentJobTitle = $user->getCurrentJobTitle();

        foreach ($counties as $item) {
            /** @var State $state */
            $state = $item->getState();
            break;
        }

        $queryString = $this->generate($state, $user, $counties, $jobTitles, $jobLevels, $jobType, $jobCategories, $worksForCity, $currentJobTitle);

        $savedSearch->setUser($user);
        $savedSearch->setType('job');
        $savedSearch->setSearchQuery($queryString);
        $savedSearch->setName('Default Search Criteria');
        $savedSearch->setIsDefault(true);

        $this->em->persist($savedSearch);
        $this->em->flush();
    }

    private function generate($state, User $user, $counties = [], $jobTitles = [], $jobLevels = [], $jobType = null, $jobCategories = [], $worksForCity = false, $currentJobTitle = null)
    {
        $queryString = '/job/search?';

        if ($state instanceof State) {
            $queryString .= urlencode('search_filter[state]') . '=' . $state->getId() . '&';
        }

        if ($counties && count($counties) > 0) {
            for ($i = 0; $i < count($counties); $i++) {
                $queryString .= urlencode('search_filter[counties][]') . '=' . $counties[$i]->getId() . '&';
            }
        }

        if ($jobTitles && count($jobTitles) > 0) {
            for ($i = 0; $i < count($jobTitles); $i++) {
                $queryString .= urlencode('search_filter[jobTitleNames][]') . '=' . $jobTitles[$i]->getId() . '&';
            }
        }

        $queryString .= urlencode('search_filter[user]') . '=' . $user->getId() . '&';

        if ($jobLevels && count($jobLevels) > 0) {
            for ($i = 0; $i < count($jobLevels); $i++) {
                $queryString .= urlencode('search_filter[jobLevels][]') . '=' . $jobLevels[$i]->getId() . '&';
            }
            $queryString .= urlencode('search_filter[jobTypes][]') . '=' . $jobType->getId() . '&';
        }

        if ($jobCategories && count($jobCategories) > 0) {
            for ($i = 0; $i < count($jobCategories); $i++) {
                $queryString .= urlencode('search_filter[jobCategories][]') . '=' . $jobCategories[$i]->getId() . '&';
            }
        }

        if ($worksForCity) {
            $queryString .= urlencode('search_filter[worksForCity]') . '=' . $worksForCity->getId() . '&';
        }

        if ($currentJobTitle) {
            $queryString .= urlencode('search_filter[currentJobTitle]') . '=' . urlencode($currentJobTitle);
        }

        return $queryString;
    }
}