<?php

namespace App\Service;

use App\Entity\JobAnnouncement;
use Doctrine\ORM\EntityManagerInterface;

class JobAnnouncementExistDecider
{
    /** @var EntityManagerInterface $em */
    private $em;

    /** @var JobAnnouncementStatusDecider $statusDecider */
    private $statusDecider;

    public function __construct(EntityManagerInterface $em, JobAnnouncementStatusDecider $statusDecider)
    {
        $this->em = $em;
        $this->statusDecider = $statusDecider;
    }

    public function decide(JobAnnouncement $jobAnnouncement)
    {
        $jobTitle = $jobAnnouncement->getJobTitle();
        $status = $this->statusDecider->decide($jobAnnouncement);
        $exists = false;
        $checkStatuses = [JobAnnouncement::STATUS_TODO, JobAnnouncement::STATUS_DRAFT, JobAnnouncement::STATUS_ACTIVE, JobAnnouncement::STATUS_ENDED];
        // Only check if the JobAnnouncement we are trying to validate is going to be in any of the CheckStatuses
        if ($jobTitle && $status && in_array($status->getId(), $checkStatuses)) {
            $existingJobAnnouncements = $this->em->getRepository(JobAnnouncement::class)->findBy([
                'city' => $jobAnnouncement->getCity(),
                'jobTitle' => $jobTitle,
            ]);


            if ($existingJobAnnouncements) {
                foreach ($existingJobAnnouncements as $existingJobAnnouncement) {
                    // We only care that a Job Announcement is either To Do, Draft, Active, or Ended
                    if (in_array($existingJobAnnouncement->getId(), $checkStatuses)) {
                        if ($jobAnnouncement->getId()) {
                            if ($jobAnnouncement->getId() != $existingJobAnnouncement->getId()) {
                                $exists = true;
                            }
                        } else {
                            $exists = true;
                        }
                    }

                }

            }

        }

        return $exists;
    }
}