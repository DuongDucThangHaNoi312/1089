<?php

namespace App\Service;

use App\Entity\JobAnnouncement;
use App\Entity\JobAnnouncement\Lookup\JobAnnouncementStatus;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;

class JobAnnouncementStatusDecider
{

    /** @var EntityManagerInterface $em * */
    private $em;

    /**
     * JobAnnouncementStatusDecider constructor.
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param JobAnnouncement $jobAnnouncement
     *
     * @return JobAnnouncementStatus|object|null
     * @throws ORMException*@throws \Exception
     */
    public function decide(JobAnnouncement $jobAnnouncement)
    {
        $status       = $jobAnnouncement->getStatus();
        $startsOn     = $jobAnnouncement->getStartsOn();
        $endsOn       = $jobAnnouncement->getEndsOn();
        $hasNoEndDate = $jobAnnouncement->getHasNoEndDate();

        if (!$status) {
            $status = $this->em->getReference(JobAnnouncementStatus::class, JobAnnouncement::STATUS_TODO);
        }

        // As long as the job is not archived
        if ($startsOn
            && ($hasNoEndDate || $endsOn)
        ) {
            $currentDate = new DateTime('now', new \DateTimeZone('UTC'));

            // Start Date in  the future;
            if ($startsOn > $currentDate) {
                // Status is Scheduled
                $status = $this->em->getReference(JobAnnouncementStatus::class, JobAnnouncement::STATUS_SCHEDULED);
            } elseif ($startsOn <= $currentDate) {

                if ($hasNoEndDate) {
                    $status = $this->em->getReference(JobAnnouncementStatus::class, JobAnnouncement::STATUS_ACTIVE);
                } else {
                    if ($endsOn > $currentDate) {
                        $status = $this->em->getReference(JobAnnouncementStatus::class, JobAnnouncement::STATUS_ACTIVE);
                    }
                    else {
                        $status = $this->em->getReference(JobAnnouncementStatus::class, JobAnnouncement::STATUS_ENDED);
                    }
                }
            }
        }

        return $status;
    }
}