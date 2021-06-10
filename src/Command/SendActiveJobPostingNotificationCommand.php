<?php

namespace App\Command;

use App\Entity\AlertedJobAnnouncement;
use App\Entity\JobAnnouncement;
use App\Entity\User\JobSeekerUser;
use App\Entity\User\SavedSearch;
use App\Repository\JobAnnouncementRepository;
use App\Repository\User\JobSeekerUserRepository;
use App\Service\EmailHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SendActiveJobPostingNotificationCommand extends Command
{
    protected static $defaultName = 'app:job-seeker:notify';

    private $em;

    private $emailHelper;

    public function __construct(?string $name = null, EntityManagerInterface $em, EmailHelper $emailHelper)
    {
        parent::__construct($name);
        $this->em          = $em;
        $this->emailHelper = $emailHelper;
    }

    protected function configure()
    {
        $this
            ->setDescription('A command to send email notification for any Active Job Posting to Job Seekers that matched.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        /** get list of job seekers (pagination) */
        /** @var JobSeekerUserRepository $jsRepo */
        $jsRepo  = $this->em->getRepository(JobSeekerUser::class);
        /** @var JobAnnouncementRepository $jaRepo */
        $jaRepo  = $this->em->getRepository(JobAnnouncement::class);
        $ajaRepo = $this->em->getRepository(AlertedJobAnnouncement::class);
        $count   = $perPage = getenv('PAGE_SIZE');
        $page    = 1;
        $sent    = 0;

        while ($count == $perPage) {
            $offset     = ($page - 1) * $perPage;
            $jobSeekers = $jsRepo->getJobSeekersForAlertNotification($offset, $perPage);

            /** find not notified job announcements for each job seeker */
            foreach ($jobSeekers as $jobSeeker) {

                $notificationPreferenceForSubmittedInterest               = $jobSeeker['notification_preference_for_submitted_interest'] ?? JobSeekerUser::NOTIFICATION_PREFERENCE_DAILY;
                $notificationPreferenceForJobsMatchingSavedSearchCriteria = $jobSeeker['notification_preference_for_jobs_matching_saved_search_criteria'] ?? JobSeekerUser::NOTIFICATION_PREFERENCE_DAILY;
                $receiveAlertForSubmittedInterest                         = $jobSeeker['for_submitted_interest'];
                $receiveAlertForJobsMatchingSavedSearchCriteria           = $jobSeeker['for_jobs_matching_saved_search_criteria'];

                $sentToCurrentJobSeeker = false;
                $jobSeekerEmail         = $jobSeeker['job_seeker_email'];
                $lastTimeNotified       = $jobSeeker['last_time_notified'] ? date_create_from_format('Y-m-d H:i:s', $jobSeeker['last_time_notified']) : null;
                $dateDiff               = $lastTimeNotified ? $lastTimeNotified->diff(new \DateTime()) : null;

                $jobSeekerId        = $jobSeeker['job_seeker_id'];
                $jobSeekerFirstName = $jobSeeker['job_seeker_firstname'];
                $allowedJobLevels   = $jobSeeker['allowed_job_levels'] ? explode(',', $jobSeeker['allowed_job_levels']) : [];

                // Send With Submitted Interest
                if ($receiveAlertForSubmittedInterest) {
                    if ($notificationPreferenceForSubmittedInterest == JobSeekerUser::NOTIFICATION_PREFERENCE_NONE) {
                        // do not send
                    } else {
                        $shouldSend       = false;

                        if ($dateDiff == null) {
                            $shouldSend = true; // never sent before
                        } elseif ($dateDiff->format('%d') >= 1 && $notificationPreferenceForSubmittedInterest == JobSeekerUser::NOTIFICATION_PREFERENCE_DAILY) {
                            $shouldSend = true;
                        } elseif ($dateDiff->format('%d') >= 7 && $notificationPreferenceForSubmittedInterest == JobSeekerUser::NOTIFICATION_PREFERENCE_WEEKLY) {
                            $shouldSend = true;
                        } elseif ($dateDiff->format('%m') >= 1 && $notificationPreferenceForSubmittedInterest == JobSeekerUser::NOTIFICATION_PREFERENCE_MONTHLY) {
                            $shouldSend = true;
                        }

                        if ($shouldSend) {
                            if ($jobSeekerEmail) {
                                $listOfSearchData     = $this->getProfileSearchDataFromJobSeeker($jobSeeker);
                                $jobAnnouncements     = $jaRepo->getJobAnnouncementsForDailyNotification($jobSeekerId, $allowedJobLevels, [$listOfSearchData], true);
                                $noOfJobAnnouncements = count($jobAnnouncements);

                                if ($noOfJobAnnouncements) {

                                    /** Job Announcements need to know city PHP timezone to properly render the time */
                                    $jobAnnouncementsWithTimezone = [];
                                    foreach ($jobAnnouncements as $ja) {
                                        $ja['jt_php_timezone'] = timezone_name_from_abbr($ja['jt_timezone'] ?? 'UTC');
                                        $jobAnnouncementsWithTimezone[] = $ja;
                                    }

                                    /** send email with these job announcements to job seeker */
                                    $this->emailHelper->sendActiveJobAlertEmail($jobSeekerEmail, $jobSeekerFirstName, $jobAnnouncementsWithTimezone, $noOfJobAnnouncements, false);

                                    /** insert these job announcement with job seeker into AlertedJobAnnouncement */
                                    $ajaRepo->insertMultipleAlertedJobAnnouncementForJobSeeker($jobSeekerId, $jobAnnouncements);

                                    $sentToCurrentJobSeeker = true;
                                }
                            }
                        }
                    }
                }

                // Send With Saved Search Criteria
                if ($receiveAlertForJobsMatchingSavedSearchCriteria) {
                    if ($notificationPreferenceForJobsMatchingSavedSearchCriteria == JobSeekerUser::NOTIFICATION_PREFERENCE_NONE) {
                        // do not send
                    } else {
                        $shouldSend       = false;

                        if ($dateDiff == null) {
                            $shouldSend = true; // never sent before
                        } elseif ($dateDiff->format('%d') >= 1 && $notificationPreferenceForJobsMatchingSavedSearchCriteria == JobSeekerUser::NOTIFICATION_PREFERENCE_DAILY) {
                            $shouldSend = true;
                        } elseif ($dateDiff->format('%d') >= 7 && $notificationPreferenceForJobsMatchingSavedSearchCriteria == JobSeekerUser::NOTIFICATION_PREFERENCE_WEEKLY) {
                            $shouldSend = true;
                        } elseif ($dateDiff->format('%m') >= 1 && $notificationPreferenceForJobsMatchingSavedSearchCriteria == JobSeekerUser::NOTIFICATION_PREFERENCE_MONTHLY) {
                            $shouldSend = true;
                        }

                        if ($shouldSend) {
                            if ($jobSeekerEmail) {
                                $listOfSearchData     = $this->getProfileSearchDataFromSavedSearches($jobSeekerId);
                                $jobAnnouncements     = $jaRepo->getJobAnnouncementsForDailyNotification($jobSeekerId, $allowedJobLevels, $listOfSearchData);
                                $noOfJobAnnouncements = count($jobAnnouncements);

                                if ($noOfJobAnnouncements) {

                                    /** Job Announcements need to know city PHP timezone to properly render the time */
                                    $jobAnnouncementsWithTimezone = [];
                                    foreach ($jobAnnouncements as $ja) {
                                        $ja['jt_php_timezone'] = timezone_name_from_abbr($ja['jt_timezone'] ?? 'UTC');
                                        $jobAnnouncementsWithTimezone[] = $ja;
                                    }

                                    /** send email with these job announcements to job seeker */
                                    $this->emailHelper->sendActiveJobAlertEmail($jobSeekerEmail, $jobSeekerFirstName, $jobAnnouncementsWithTimezone, $noOfJobAnnouncements);

                                    /** insert these job announcement with job seeker into AlertedJobAnnouncement */
                                    $ajaRepo->insertMultipleAlertedJobAnnouncementForJobSeeker($jobSeekerId, $jobAnnouncements);

                                    $sentToCurrentJobSeeker = true;
                                }
                            }
                        }
                    }
                }

                if ($sentToCurrentJobSeeker) {
                    $sent++;
                    $io->note('Notification sent to ' . $jobSeekerEmail);
                }
            }

            $page++;
            $count = count($jobSeekers);
        }

        if ($sent) {
            $io->success('Notifications sent successfully to ' . $sent . ' job seeker(s).');
        }
        else {
            $io->success('No new Job Alert sent.');
        }

    }


    /**
     * @param $jobSeekerOptions
     *
     * @return array
     */
    private function getProfileSearchDataFromJobSeeker($jobSeekerOptions)
    {

        $searchData = [];

        if ( ! empty($jobSeekerOptions['works_for_city'])) {
            $searchData['works_for_city'] = $jobSeekerOptions['works_for_city'];
        }
        if ( ! empty($jobSeekerOptions['state'])) {
            $searchData['state'] = $jobSeekerOptions['state'];
        }
        if ( ! empty($jobSeekerOptions['counties'])) {
            $searchData['counties'] = explode(',', $jobSeekerOptions['counties']);
        }
        if ( ! empty($jobSeekerOptions['cities'])) {
            $searchData['cities'] = explode(',', $jobSeekerOptions['cities']);
        }
        if ( ! empty($jobSeekerOptions['interested_job_title_names'])) {
            $searchData['jobTitleNames'] = explode(',', $jobSeekerOptions['interested_job_title_names']);
        }
        if ( ! empty($jobSeekerOptions['interested_job_levels'])) {
            $searchData['jobLevels'] = explode(',', $jobSeekerOptions['interested_job_levels']);
        }
        if ( ! empty($jobSeekerOptions['interested_job_type'])) {
            $searchData['jobTypes'] = explode(',', $jobSeekerOptions['interested_job_type']);
        }
        if ( ! empty($jobSeekerOptions['interested_job_categories'])) {
            $searchData['jobCategories'] = explode(',', $jobSeekerOptions['interested_job_categories']);
        }

        return $searchData;
    }


    /**
     * @param $jobSeekerId
     *
     * @return array
     */
    private function getProfileSearchDataFromSavedSearches($jobSeekerId)
    {
        $savedSearches = $this->em->getRepository(SavedSearch::class)->findBy([
            'type' => SavedSearch::JOB_SEARCH_TYPE,
            'user' => $jobSeekerId
        ]);

        $listOfSearchData = [];

        /** @var SavedSearch $search */
        foreach ($savedSearches as $search) {

            $ss          = urldecode($search->getSearchQuery());
            $searchData  = [];
            $queryString = explode('?', $ss);

            if (count($queryString) > 1) {

                parse_str($queryString[1], $params);

                foreach ($params as $key => $value) {
                    if ($key != 'search_filter') {
                        $searchData[$key] = $value;
                    }
                    else {
                        foreach ($value as $k => $v) {
                            $searchData[$k] = $v;
                        }
                    }
                }
            }

            if (count($searchData)) {

                if ( ! isset($searchData['user'])) {
                    $searchData['user'] = $jobSeekerId;
                }

                $listOfSearchData[] = $searchData;
            }
        }

        return $listOfSearchData;
    }
}
