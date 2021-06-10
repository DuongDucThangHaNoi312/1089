<?php

namespace App\Tests;

use App\Command\SendActiveJobPostingNotificationCommand;
use App\Entity\JobAnnouncement;
use App\Entity\User\JobSeekerUser;
use App\Repository\JobAnnouncementRepository;
use App\Repository\User\JobSeekerUserRepository;
use App\Service\EmailHelper;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class JobAnnouncementEmailResultsTest extends WebTestCase
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    protected function setUp()
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    public function testGetJobAnnouncementsFromSubmittedInterest()
    {
        /** @var JobSeekerUserRepository $jobSeekerRepository */
        $jobSeekerRepository = $this->entityManager->getRepository(JobSeekerUser::class);
        $jobSeekers = $jobSeekerRepository->getJobSeekersForAlertNotification(0, 1);

        $jobSeeker = $jobSeekers[0];

        $jobSeekerId        = $jobSeeker['job_seeker_id'];
        $allowedJobLevels   = $jobSeeker['allowed_job_levels'] ? explode(',', $jobSeeker['allowed_job_levels']) : [];

        /** @var JobAnnouncementRepository $jobAnnouncementRepository */
        $jobAnnouncementRepository = $this->entityManager->getRepository(JobAnnouncement::class);


        $listOfSearchData = $this->getListOfSearchData("getProfileSearchDataFromJobSeeker", $jobSeeker);
        $this->assertArrayHasKey('state', $listOfSearchData);

        $jobAnnouncements = $jobAnnouncementRepository->getJobAnnouncementsForDailyNotification($jobSeekerId, $allowedJobLevels, [$listOfSearchData], true);
        $noOfJobAnnouncements = count($jobAnnouncements);

        $this->assertSame(2, $noOfJobAnnouncements, print_r($jobAnnouncements, true));

        $this->assertSame('Bus Driver', $jobAnnouncements[0]['ja_name']);
        $this->assertSame('Cashier', $jobAnnouncements[1]['ja_name']);
    }


    public function testGetJobAnnouncementsFromSavedSearchesOnlyDefaultSearchCriteria()
    {
        /** @var JobSeekerUserRepository $jobSeekerRepository */
        $jobSeekerRepository = $this->entityManager->getRepository(JobSeekerUser::class);
        $jobSeekers = $jobSeekerRepository->getJobSeekersForAlertNotification(0, 1);

        $jobSeeker = $jobSeekers[0];

        $jobSeekerId        = $jobSeeker['job_seeker_id'];
        $allowedJobLevels   = $jobSeeker['allowed_job_levels'] ? explode(',', $jobSeeker['allowed_job_levels']) : [];

        /** @var JobAnnouncementRepository $jobAnnouncementRepository */
        $jobAnnouncementRepository = $this->entityManager->getRepository(JobAnnouncement::class);

        $listOfSearchData = $this->getListOfSearchData("getProfileSearchDataFromSavedSearches", $jobSeekerId);

        $jobAnnouncements = $jobAnnouncementRepository->getJobAnnouncementsForDailyNotification($jobSeekerId, $allowedJobLevels, $listOfSearchData);
        $noOfJobAnnouncements = count($jobAnnouncements);

        $this->assertSame(3, $noOfJobAnnouncements, print_r($jobAnnouncements, true));

        $this->assertStringStartsWith("Accountant", $jobAnnouncements[0]['ja_name']);
        $this->assertStringStartsWith("Accountant II", $jobAnnouncements[1]['ja_name']);
        $this->assertStringStartsWith("Buyer", $jobAnnouncements[2]['ja_name']);
    }

    public function testGetJobAnnouncementsFromSavedSearches() {
        /** @var JobSeekerUserRepository $jobSeekerRepository */
        $jobSeekerRepository = $this->entityManager->getRepository(JobSeekerUser::class);
        $jobSeekers = $jobSeekerRepository->getJobSeekersForAlertNotification(1, 1);

        $jobSeeker = $jobSeekers[0];

        $jobSeekerId        = $jobSeeker['job_seeker_id'];
        $allowedJobLevels   = $jobSeeker['allowed_job_levels'] ? explode(',', $jobSeeker['allowed_job_levels']) : [];

        /** @var JobAnnouncementRepository $jobAnnouncementRepository */
        $jobAnnouncementRepository = $this->entityManager->getRepository(JobAnnouncement::class);

        $listOfSearchData = $this->getListOfSearchData("getProfileSearchDataFromSavedSearches", $jobSeekerId);

        $jobAnnouncements = $jobAnnouncementRepository->getJobAnnouncementsForDailyNotification($jobSeekerId, $allowedJobLevels, $listOfSearchData);
        $noOfJobAnnouncements = count($jobAnnouncements);

        $this->assertSame(4, $noOfJobAnnouncements, print_r($jobAnnouncements, true));

        $this->assertStringStartsWith("Accountant", $jobAnnouncements[0]['ja_name']);
        $this->assertStringStartsWith("Accountant II", $jobAnnouncements[1]['ja_name']);
        $this->assertStringStartsWith("Buyer", $jobAnnouncements[2]['ja_name']);
        $this->assertStringStartsWith("Bus Driver", $jobAnnouncements[3]['ja_name']);
    }

    /**
     * @param string $methodName
     * @param mixed $parameters
     * @return array
     * @throws \ReflectionException
     */
    protected function getListOfSearchData(string $methodName, $parameters = null) {
        $mockEmailHelper = $this->createMock(EmailHelper::class);
        $sendActiveJobPostingNotificationCommand = new SendActiveJobPostingNotificationCommand(null, $this->entityManager, $mockEmailHelper);

        $refl = new \ReflectionMethod(get_class($sendActiveJobPostingNotificationCommand), $methodName);
        $refl->setAccessible(true);

        $listOfSearchData = $refl->invoke($sendActiveJobPostingNotificationCommand, $parameters);
        return $listOfSearchData;
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->entityManager->close();
        $this->entityManager = null;
    }
}
