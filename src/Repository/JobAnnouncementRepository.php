<?php

namespace App\Repository;

use App\Entity\City;
use App\Entity\JobAnnouncement;
use App\Entity\JobAnnouncement\Lookup\JobAnnouncementStatus;
use App\Entity\User\JobSeekerUser;
use App\Service\ProfileDataSearchHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method JobAnnouncement|null find($id, $lockMode = null, $lockVersion = null)
 * @method JobAnnouncement|null findOneBy(array $criteria, array $orderBy = null)
 * @method JobAnnouncement[]    findAll()
 * @method JobAnnouncement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JobAnnouncementRepository extends ServiceEntityRepository
{
    private $profileDataSearchHelper;

    public function __construct(RegistryInterface $registry, ProfileDataSearchHelper $profileDataSearchHelper)
    {
        parent::__construct($registry, JobAnnouncement::class);
        $this->profileDataSearchHelper = $profileDataSearchHelper;
    }

    public function getQueryJobAnnouncementsToPostForCity(City $city, $jobTitle = null)
    {
        $qb = $this->createQueryBuilder('ja')
            ->select('ja.id, ja.isAlert, jt.id as job_title_id, jtn.name as job_title, d.name AS department, t.name as type, s.name as status, ja.endDateDescription as endDateDescription,
        a.id as assigned_user_id, a.firstname, a.lastname, CONCAT(a.firstname, \' \', a.lastname) as assignedTo, a.id as assignedToId, ja.startsOn, ja.endsOn, ja.hasNoEndDate, ja.endDateDescription, division.name as divisionName')
            ->join('ja.jobTitle', 'jt')
            ->join('jt.jobTitleName', 'jtn')
            ->join('jt.department', 'd')
            ->join('jt.type', 't')
            ->join('ja.status', 's')
            ->leftJoin('ja.assignedTo', 'a')
            ->leftJoin('jt.division', 'division')
            ->where('(jt.city = :city OR ja.city = :city) AND (ja.status = :todo OR ja.status = :draft OR ja.status = :scheduled)')
            ->andWhere('jt.isHidden = false')
            ->setParameters(['city' => $city, 'todo' => JobAnnouncement::STATUS_TODO, 'draft' => JobAnnouncement::STATUS_DRAFT, 'scheduled' => JobAnnouncement::STATUS_SCHEDULED])
        ;

        if ($jobTitle) {
            $qb
                ->andWhere('jtn.name LIKE :jobTitle')
                ->setParameter('jobTitle', '%'.$jobTitle.'%')
            ;
        }

        return $qb;
    }

    /**
     * @param array $jobTitles
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilderForFindByInterestedJobTitles($jobTitles = []) {
        return $this->createQueryBuilder('ja')
            ->andWhere('ja.status = :status')
            ->setParameter('status', JobAnnouncement::STATUS_ACTIVE)
            ->leftJoin('ja.jobTitle', 'jt')
            ->andWhere('jt.id IN (:jobTitles)')
            ->setParameter('jobTitles', $jobTitles);
    }

    public function getJobAnnouncementCountMatchingProfile(JobSeekerUser $user, $searchData = null) {
        $filterQueryBuilder = $this->getQueryBuilderWithSearchFilterData($searchData, false)
            ->select('job_announcement.id');

        $mainQueryBuilder = $this->createQueryBuilder('j')
            ->select('COUNT(j.id)')
            ->andWhere('j.id IN ('.$filterQueryBuilder->getDQL().')');;

        // Need to redefine the parameters since I included the DQL within the main query
        /** @var Query\Parameter $v */
        foreach ($filterQueryBuilder->getParameters() as $v) {
            $mainQueryBuilder->setParameter($v->getName(), $v->getValue());
        }

        return $mainQueryBuilder
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findForDashboard(JobSeekerUser $user, $interestedJobTitles = [], $savedJobAnnouncements = [], $excludedJobAnnouncements = [], $maxResults = 3, $count = false) {

        //$excludedJobAnnouncements = array_unique(array_merge($savedJobAnnouncements, $excludedJobAnnouncements), SORT_REGULAR);

        $dashboardQueryBuilder = $this->getQueryBuilderForFindByInterestedJobTitles($interestedJobTitles)
            ->select('ja.id');

        $mainQueryBuilder = $this->createQueryBuilder('j');
            if ($count) {
                $mainQueryBuilder->select('COUNT(j.id)');
            }
        $mainQueryBuilder
            ->andWhere('j.id IN ('.$dashboardQueryBuilder->getDQL().')');
            if (count($excludedJobAnnouncements) > 0) {
                $mainQueryBuilder
                    ->andWhere('j.id NOT IN (:excludeJobAnnouncements)')
                    ->setParameter('excludeJobAnnouncements', $excludedJobAnnouncements);
            }

        // Limit by Job Level
        if ($user->getSubscription() && $user->getSubscription()->getSubscriptionPlan() && count($user->getSubscription()->getSubscriptionPlan()->getAllowedJobLevels()) > 0) {
            $mainQueryBuilder
                ->leftJoin('j.jobTitle', 'jobtitle')
                ->andWhere('jobtitle.level IN (:jobLevels)')
                ->setParameter('jobLevels', $user->getSubscription()->getSubscriptionPlan()->getAllowedJobLevels());

        }

        // Need to redefine the parameters since I included the DQL within the main query
        /** @var Query\Parameter $v */
        foreach ($dashboardQueryBuilder->getParameters() as $v) {
            $mainQueryBuilder->setParameter($v->getName(), $v->getValue());
        }

        if ($count) {
            return $mainQueryBuilder
                ->getQuery()
                ->getSingleScalarResult();
        }
        return $mainQueryBuilder
            ->getQuery()
            ->setMaxResults($maxResults)
            ->getResult();
    }

    /**
     * @param City $city
     * @param JobAnnouncementStatus $jobAnnouncementStatus
     * @param null $jobTitle
     * @return mixed
     */
    public function getQueryJobAnnouncementsForCityAndStatus(City $city, JobAnnouncementStatus $jobAnnouncementStatus, $jobTitle = null)
    {
        $qb = $this->createQueryBuilder('ja')
            ->select('ja.id, ja.isAlert, jt.id as job_title_id, jtn.name as job_title, d.name AS department, t.name as type, s.name as status, ja.endDateDescription as endDateDescription,
                a.id as assigned_user_id, CONCAT(a.firstname, \' \', a.lastname) as assignedTo, a.id as assignedToId, ja.startsOn, ja.endsOn, ja.hasNoEndDate, ja.endDateDescription, COUNT(jav) as viewCount, division.name as divisionName')
            ->join('ja.jobTitle', 'jt')
            ->join('jt.jobTitleName', 'jtn')
            ->join('jt.department', 'd')
            ->join('jt.type', 't')
            ->join('ja.status', 's')
            ->leftJoin('ja.assignedTo', 'a')
            ->leftJoin('ja.views', 'jav')
            ->leftJoin('jt.division', 'division')
            ->where('jt.city = :city OR ja.city = :city')
            ->andWhere('ja.status = :status')
            ->andWhere('jt.isHidden = false')
            ->groupBy('ja.id')
            ->setParameter('city', $city)
            ->setParameter('status', $jobAnnouncementStatus)
        ;

        if ($jobTitle) {
            $qb
                ->andWhere('jtn.name LIKE :jobTitle')
                ->setParameter('jobTitle', '%'.$jobTitle.'%')
            ;
        }

        return $qb;
    }

    public function getJobsToPostForDashboardForCity(City $city, $maxResults = 4) {
        $qb = $this->createQueryBuilder('job_announcement')
            ->select('
                    job_announcement.id as job_announcement_id, 
                    jtn.name as job_title_name, 
                    s.name as status, 
                    s.slug as slug,
                    job_announcement.startsOn'
            )
            ->leftJoin('job_announcement.jobTitle', 'job_title')
            ->leftJoin('job_title.jobTitleName', 'jtn')
            ->leftJoin('job_title.city', 'city')
            ->andWhere('city = :city OR job_announcement.city = :city')
            ->andWhere('job_title.isHidden = false')
            ->setParameter('city', $city)
            ->leftJoin('job_announcement.status', 's')
            ->andWhere('job_announcement.status IN (:statuses)')
            ->setParameter('statuses', [JobAnnouncement::STATUS_SCHEDULED, JobAnnouncement::STATUS_TODO, JobAnnouncement::STATUS_DRAFT])
            ->orderBy('jtn.name', 'ASC');

        return $qb
            ->getQuery()
            ->setMaxResults($maxResults)
            ->getResult();

    }

    public function getActiveJobAnnouncementsForCity(City $city) {
        $qb = $this->createQueryBuilder('job_announcement')
            ->where('city = :city')
            ->setParameter('city', $city)
            ->andWhere('job_announcement.status IN (:statuses)')
            ->setParameter('statuses', [JobAnnouncement::STATUS_ACTIVE])
            ->orderBy('jtn.name', 'ASC');

        return $qb
            ->getQuery()
            ->getResult();

    }

    public function getCountActiveJobAnnouncementsForCity(City $city) {
        return $this->createQueryBuilder('job_announcement')
            ->select('COUNT(job_announcement.id)')
            ->join('job_announcement.jobTitle', 'job_title')
            ->where('job_title.city = :city OR job_announcement.city = :city')
            ->andWhere('job_announcement.status IN (:statuses)')
            ->andWhere('job_announcement.isPostedByCGJ = false')
            ->setParameter('city', $city)
            ->setParameter('statuses', [JobAnnouncement::STATUS_ACTIVE])
            ->getQuery()
            ->getSingleScalarResult()
        ;


    }

    public function getActiveJobAnnouncementsForDashboard(City $city, $maxResults = 4) {
        $qb = $this->createQueryBuilder('job_announcement')
            ->select('
                    job_announcement.id as job_announcement_id, 
                    jtn.name as job_title_name, 
                    job_announcement.endsOn,
                    job_announcement.hasNoEndDate,
                    job_announcement.endDateDescription,
                    COUNT(views.id) as job_announcement_views'
            )
            ->leftJoin('job_announcement.jobTitle', 'job_title')
            ->leftJoin('job_title.jobTitleName', 'jtn')
            ->leftJoin('job_title.city', 'city')
            ->leftJoin('job_announcement.views', 'views')
            ->andWhere('city = :city OR job_announcement.city = :city')
            ->andWhere('job_title.isHidden = false')
            ->setParameter('city', $city)
            ->andWhere('job_announcement.status IN (:statuses)')
            ->setParameter('statuses', [JobAnnouncement::STATUS_ACTIVE])
            ->groupBy('job_announcement.id')
            ->orderBy('jtn.name', 'ASC');

        return $qb
            ->getQuery()
            ->setMaxResults($maxResults)
            ->getResult();

    }

    public function getEndedJobAnnouncementsForDashboard(City $city, $maxResults = 4) {
        $qb = $this->createQueryBuilder('job_announcement')
            ->select('
                    job_announcement.id as job_announcement_id, 
                    jtn.name as job_title_name, 
                    job_announcement.endsOn,
                    COUNT(views.id) as job_announcement_views'
            )
            ->leftJoin('job_announcement.jobTitle', 'job_title')
            ->leftJoin('job_title.jobTitleName', 'jtn')
            ->leftJoin('job_title.city', 'city')
            ->leftJoin('job_announcement.views', 'views')
            ->andWhere('city = :city OR job_announcement.city = :city')
            ->andWhere('job_title.isHidden = false')
            ->setParameter('city', $city)
            ->andWhere('job_announcement.status IN (:statuses)')
            ->setParameter('statuses', [JobAnnouncement::STATUS_ENDED])
            ->groupBy('job_announcement.id')
            ->orderBy('jtn.name', 'ASC');

        return $qb
            ->getQuery()
            ->setMaxResults($maxResults)
            ->getResult();

    }


    public function getTotalJobsToPost(City $city): int {
        $qb = $this->createQueryBuilder('job_announcement')
            ->select('COUNT(job_announcement.id) as total')
            ->leftJoin('job_announcement.jobTitle', 'job_title')
            ->leftJoin('job_title.city', 'city')
            ->andWhere('city = :city OR job_announcement.city = :city')
            ->andWhere('job_title.isHidden = false')
            ->setParameter('city', $city)
            ->andWhere('job_announcement.status IN (:statuses)')
            ->setParameter('statuses', [JobAnnouncement::STATUS_SCHEDULED, JobAnnouncement::STATUS_TODO, JobAnnouncement::STATUS_DRAFT]);

        return $qb
            ->getQuery()
            ->getSingleScalarResult();

    }

    public function getTotalActiveJobAnnouncements(City $city): int {
        return $this->createQueryBuilder('ja')
            ->leftJoin('ja.jobTitle', 'job_title')
            ->leftJoin('job_title.city', 'city')
            ->select('COUNT(ja) as job_announcement_total')
            ->andWhere('city = :city OR ja.city = :city')
            ->andWhere('ja.status = :status')
            ->andWhere('job_title.isHidden = false')
            ->setParameter('city', $city)
            ->setParameter('status', JobAnnouncement::STATUS_ACTIVE)
            ->getQuery()
            ->setHydrationMode(Query::HYDRATE_SINGLE_SCALAR)
            ->getSingleScalarResult();
    }

    public function getTotalEndedJobAnnouncements(City $city): int {
        return $this->createQueryBuilder('ja')
            ->leftJoin('ja.jobTitle', 'job_title')
            ->leftJoin('job_title.city', 'city')
            ->select('COUNT(ja) as job_announcement_total')
            ->andWhere('city = :city OR ja.city = :city')
            ->andWhere('ja.status = :status')
            ->andWhere('job_title.isHidden = false')
            ->setParameter('city', $city)
            ->setParameter('status', JobAnnouncement::STATUS_ENDED)
            ->getQuery()
            ->setHydrationMode(Query::HYDRATE_SINGLE_SCALAR)
            ->getSingleScalarResult();
    }


    /**
     * @param null $searchData
     * @param bool $selectiveFields
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilderWithSearchFilterData($searchData = null, $selectiveFields = true, $count = false) {
        $qb = $this->createQueryBuilder('job_announcement');
        if ($selectiveFields) {
            $qb
                ->select('
                    job_announcement.id as jobAnnouncementId,
                    job_announcement.description as jobAnnouncementDescription,
                    job_announcement.isAlert as isAlert,
                    job_announcement.hasNoEndDate,
                    job_announcement.endDateDescription,
                    job_announcement.wageSalaryHigh,
                    job_announcement.wageSalaryLow,
                    job_announcement.startsOn,
                    job_announcement.isPostedByCGJ,
                    wage_salary_unit.name as wageSalaryUnitName,
                    job_announcement.wageRangeDependsOnQualifications,
                    job_announcement.applicationUrl,
                    job_announcement.applicationDeadline,
                    job_announcement.attachedDocument,
                    job_title.id as jobTitleId,
                    job_title.isClosedPromotional as isClosedPromotional,
                    jtn.name as jobTitleName,
                    city.id as cityId,
                    city.name as cityName,
                    city.slug as citySlug,
                    city.timezone as cityTimezone,
                    city.cgjPostsJobs as cityCgjPostsJobs,
                    subscription.expiresAt as expiresAt,
                    subscription.cancelledAt as cancelledAt,
                    department.name as departmentName,
                    division.name as divisionName,
                    type.name as typeName,
                    GROUP_CONCAT(DISTINCT category.name SEPARATOR \', \') as categoryName,
                    level.name as levelName,
                    level.id as levelId
                    ');
        }

        $qb
            ->join('job_announcement.jobTitle', 'job_title')
            ->join('job_title.jobTitleName', 'jtn')
            ->leftJoin('job_announcement.wageSalaryUnit', 'wage_salary_unit')
            ->join('job_title.department', 'department')
            ->join('job_title.level', 'level')
            ->join('job_title.city', 'city')
            ->leftJoin('city.subscription', 'subscription')
            ->join('city.counties', 'counties')
            ->leftJoin('job_title.division', 'division')
            ->join('job_title.type', 'type')
            ->leftJoin('job_title.category', 'category')
            ->where('job_announcement.status = '.JobAnnouncement::STATUS_ACTIVE)
            ->andWhere('job_title.isHidden = false')
            ->andWhere('city.isSuspended = false')
            ->andWhere('counties.isActive = 1')
            ->orderBy('jtn.name, city.name', 'ASC')
        ;

        if ($count) {
            $qb->select('COUNT(DISTINCT job_announcement.id)');
        }
        else {
            $qb->groupBy('job_announcement.id');
        }

        $this->profileDataSearchHelper->filterQueryBuilderByProfileData($qb, 'job_title', 'job_announcement', 'city', 'jtn', $searchData);

        return $qb;
    }

    /**
     * @param null $searchData
     *
     * @return Query
     */
    public function getQueryWithSearchFilterData($searchData = null) {
        $qb = $this->getQueryBuilderWithSearchFilterData($searchData, true);
        return $qb->getQuery();
    }

    public function getCountWithSearchFilterData($searchData = null) {
        $qb = $this->getQueryBuilderWithSearchFilterData($searchData, false, true);
        return $qb->getQuery()->getSingleScalarResult();
    }


    /**
     * GET ACTIVE JOB ANNOUNCEMENTS FOR DAILY NOTIFICATION
     *
     * @param $jobSeekerId
     * @param $allowedJobLevels
     * @param $listOfSearchData
     *
     * @param bool $submittedInterestOnly
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function findJobAnnouncementsForDailyNotificationQueryBuilder($jobSeekerId, $allowedJobLevels, $listOfSearchData, $submittedInterestOnly = false)
    {
        /**
         * JobAnnouncement.Status = Active
         * and JobAnnouncement.ActiveDate <= today
         * and JobAnnouncementNotification = hasNotBeenSent
         * and JobAnnouncement.JobTitle.JobLevel IN (jobSeeker.Subscription.SubscriptionPlan.allowedLevels)
         */
        $qb = $this->createQueryBuilder('ja')
                   ->select('DISTINCT ja.id as ja_id, 
                       ja.applicationDeadline as ja_deadline, 
                       ja.wageRangeDependsOnQualifications as ja_doq, 
                       ja.wageSalaryLow as ja_salary_low, 
                       ja.wageSalaryHigh as ja_salary_high, 
                       ja.endDateDescription as ja_end_date_description,
                       unit.name as ja_salary_unit, 
                       jtn.name as ja_name, 
                       city.slug as jt_city_slug, 
                       city.name as jt_city, 
                       city.timezone as jt_timezone,
                       state.name as jt_state, 
                       department.name as department_name')
                   ->join('ja.jobTitle', 'jt')
                   ->join('jt.city', 'city')
                   ->leftJoin('city.counties', 'counties')
                   ->leftJoin('counties.state', 'state')

                   ->join('jt.level', 'level')
                   ->join('jt.jobTitleName', 'jtn')
                   ->leftJoin('ja.wageSalaryUnit', 'unit')
                   ->leftJoin('jt.department', 'department')
        ;

        /** $alertedJobAnnouncementsQb to get job announcement that sent to this job seeker */
        $alertedJobAnnouncementsQb = $this->_em->createQueryBuilder()
                           ->select('sub_ja.id')
                           ->from('App\Entity\AlertedJobAnnouncement', 'sub_aja')
                           ->join('sub_aja.jobAnnouncement', 'sub_ja')
                           ->join('sub_aja.jobSeeker', 'sub_js')
                           ->where('sub_js.id = :jobSeekerId');

        if ( ! $submittedInterestOnly) {
            $jaIdList = [];
            foreach ($listOfSearchData as $searchData) {
                $subQb = $this->findJobAnnouncementsForDailyNotificationSubQuery($jobSeekerId, $allowedJobLevels, $searchData, $alertedJobAnnouncementsQb);
                $results = $subQb->getQuery()->getResult();
                foreach ($results as $result) {
                    $id = $result['id'];

                    if (!in_array($id, $jaIdList)) {
                        $jaIdList[] = $id;
                    }

                }
            }

            if (count($jaIdList) > 0) {
                $qb->andWhere('ja.id IN (:jaIds)')
                   ->setParameter('jaIds', $jaIdList);
            }
            else {
                $qb->andWhere('1 = 0'); // IF there is no job announcement matched, then should return nothing here, without this, the query will return all Job Announcements.
            }
        }
        else {
            /** join SubmittedJobTitleInterest */
            $qb->leftJoin('jt.submittedJobTitleInterests', 'interested_jt')
               ->leftJoin('interested_jt.jobSeekerUser', 'jsu')
               ->orWhere('jsu.id = :jobSeekerId');
        }

        $qb->andWhere($qb->expr()->notIn('ja.id', $alertedJobAnnouncementsQb->getDQL()))
            ->andWhere('ja.status = :status')
            ->setParameter('status', JobAnnouncement::STATUS_ACTIVE)
            ->setParameter('jobSeekerId', $jobSeekerId)
            ->andWhere('ja.startsOn <= CURRENT_DATE()')
            ->andWhere('ja.applicationDeadline > CURRENT_DATE() OR ja.hasNoEndDate = 1')
            ->andWhere('counties.isActive = 1')
        ;

        return $qb;
    }

    private function findJobAnnouncementsForDailyNotificationSubQuery($jobSeekerId, $allowedJobLevels, $searchData, $alertedJobAnnouncementsQb) {
        /**
         * JobAnnouncement.Status = Active
         * and JobAnnouncement.ActiveDate <= today
         * and JobAnnouncementNotification = hasNotBeenSent
         * and JobAnnouncement.JobTitle.JobLevel IN (jobSeeker.Subscription.SubscriptionPlan.allowedLevels)
         */
        $qb = $this->createQueryBuilder('ja')
                   ->select('DISTINCT ja.id')
                   ->join('ja.jobTitle', 'jt')
                   ->join('jt.city', 'city')
                   ->leftJoin('city.counties', 'counties')
                   ->leftJoin('jt.level', 'level')
                   ->leftJoin('jt.jobTitleName', 'jtn')
                   ->leftJoin('ja.city', 'jact')
                   ->leftJoin('ja.state', 'jast')
                   ->leftJoin('ja.wageSalaryUnit', 'unit')
                   ->andWhere('ja.status = :status')
                   ->setParameter('status', JobAnnouncement::STATUS_ACTIVE)
                   ->andWhere('counties.isActive = 1')
                   ->andWhere('ja.startsOn <= CURRENT_DATE()');

        $qb->andWhere($qb->expr()->notIn('ja.id', $alertedJobAnnouncementsQb->getDQL()))
           ->setParameter('jobSeekerId', $jobSeekerId);

        if ( ! empty($allowedJobLevels)) {
            $qb->andWhere('level.id IN (:allowedLevels)')
               ->setParameter('allowedLevels', $allowedJobLevels);
        }

        $this->profileDataSearchHelper->filterQueryBuilderByProfileData($qb, 'jt', 'ja', 'city', 'jtn', $searchData);

        return $qb;
    }

    /**
     * @param $offset
     * @param $limit
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getEndedOrArchivedJobAnnouncements($offset, $limit)
    {
        $notActiveStatuses = [
            JobAnnouncement::STATUS_ARCHIVED,
            JobAnnouncement::STATUS_ENDED
        ];

        return $this->createQueryBuilder('ja')
            ->andWhere('ja.status  IN (:notActiveStatuses)')
            ->setParameter('notActiveStatuses', $notActiveStatuses)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()->getResult()
        ;
    }

    /**
     * @param $jobSeekerId
     * @param $allowedJobLevels
     * @param $searchData
     * @param bool $submittedInterestOnly
     *
     * @return mixed
     */
    public function getJobAnnouncementsForDailyNotification($jobSeekerId, $allowedJobLevels, $searchData, $submittedInterestOnly = false)
    {
        return $this->findJobAnnouncementsForDailyNotificationQueryBuilder($jobSeekerId, $allowedJobLevels, $searchData, $submittedInterestOnly)
                   ->getQuery()
                   ->getResult();
    }


    /**
     * @param $jobTitleId
     *
     * @return bool
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isJobTitleVacant($jobTitleId)
    {

        $notActiveStatuses = [
            JobAnnouncement::STATUS_ARCHIVED,
            JobAnnouncement::STATUS_ENDED
        ];

        $qb = $this->createQueryBuilder('ja')
                   ->select('COUNT(ja.id)')
                   ->andWhere('ja.jobTitle = :jobTitle')
                   ->andWhere('ja.status NOT IN (:notActiveStatuses)')
                   ->setParameter('jobTitle', $jobTitleId)
                   ->setParameter('notActiveStatuses', $notActiveStatuses);

        $count = $qb->getQuery()->getSingleScalarResult();

        return $count ? true : false;
    }

    public function getJobTitleIDsNotArchivedForJobTitleIds(array $jobTitleIds)
    {
        $qb = $this->createQueryBuilder('ja')
            ->select('jt.id')
            ->join('ja.jobTitle', 'jt')
            ->andWhere('ja.status != :status')
            ->andWhere('ja.jobTitle IN (:jobTitleIds)')
            ->setParameter('status', JobAnnouncement::STATUS_ARCHIVED)
            ->setParameter('jobTitleIds', $jobTitleIds)
        ;

        return $qb
            ->getQuery()
            ->getResult('ColumnHydrator');
    }

    public function getJobAnnouncementsForSitemap()
    {
        return $this->createQueryBuilder('ja')
                    ->where('ja.isAlert = 0')
                    ->andWhere('ja.status = :status')
                    ->setParameter('status',JobAnnouncement::STATUS_ACTIVE)
                    ->getQuery()
                    ->getResult();
    }
}
