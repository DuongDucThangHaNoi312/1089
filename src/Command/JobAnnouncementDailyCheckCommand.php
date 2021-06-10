<?php

namespace App\Command;

use App\Entity\JobAnnouncement;
use App\Entity\JobAnnouncement\Lookup\JobAnnouncementStatus;
use App\Repository\JobAnnouncementRepository;
use App\Service\JobAnnouncementStatusDecider;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class JobAnnouncementDailyCheckCommand extends Command
{
    protected static $defaultName = 'app:job-announcement:daily-check';

    private $jobAnnouncementRepository;
    private $jobAnnouncementStatusDecider;
    private $em;

    public function __construct(JobAnnouncementRepository $jobAnnouncementRepository, JobAnnouncementStatusDecider $jobAnnouncementStatusDecider, EntityManagerInterface $em, string $name = null)
    {
        parent::__construct($name);

        $this->jobAnnouncementRepository = $jobAnnouncementRepository;
        $this->jobAnnouncementStatusDecider = $jobAnnouncementStatusDecider;
        $this->em = $em;

    }

    protected function configure()
    {
        $this
            ->setDescription('Checks status of job announcements and updates according to business rules.')
//            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
//            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws ORMException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        //attempt to activate jobs scheduled to start at or before now, ending after now, that are currently scheduled
        $announcementsToActivate = $this->jobAnnouncementRepository->createQueryBuilder('ja')
            ->where('ja.startsOn <= :now')
            ->andWhere('ja.endsOn > :now OR ja.endsOn IS NULL')
            ->andWhere('ja.status = :scheduledStatus')
            ->setParameter('now', new DateTime('now', new \DateTimeZone('UTC')))
            ->setParameter('scheduledStatus', JobAnnouncement::STATUS_SCHEDULED)
            ->getQuery()
            ->getResult();

        $activateCounter = 0;
        foreach ($announcementsToActivate as $a) {
            /** @var JobAnnouncement $a */
            $status = $this->jobAnnouncementStatusDecider->decide($a);
            if ($status !== $a->getStatus()) {
                $io->note('Job Announcement ID: '.$a->getId().' should have status '.$status.' but is currently '.$a->getStatus().'. Updating...');
                $activateCounter++;
            }
            $a->setStatus($status);
            $jobTitle = $a->getJobTitle();
            switch ($status->getId()) {
                case JobAnnouncement::STATUS_ACTIVE:
                    $jobTitle->setIsVacant(true);
                    break;
                default:
                    break;

            }
            $this->em->persist($a);
            $this->em->persist($jobTitle);
        }
        $this->em->flush();

        //attempt to end jobs before now, that are currently active
        $announcementsToEnd = $this->jobAnnouncementRepository->createQueryBuilder('ja')
            ->where('ja.endsOn <= :now AND ja.endsOn IS NOT NULL')
            ->andWhere('ja.status = :activeStatus')
            ->setParameter('now', new DateTime('now', new \DateTimeZone('UTC')))
            ->setParameter('activeStatus', JobAnnouncement::STATUS_ACTIVE)
            ->getQuery()
            ->getResult();
        $endCounter = 0;
        foreach ($announcementsToEnd as $a) {
            /** @var JobAnnouncement $a */
            $status = $this->jobAnnouncementStatusDecider->decide($a);
            if ($status !== $a->getStatus()) {
                $io->note('Job Announcement ID: '.$a->getId().' should have status '.$status.' but is currently '.$a->getStatus().'. Updating...');
                $endCounter++;
            }
            $a->setStatus($status);
            $this->em->persist($a);
        }
        $this->em->flush();

        //attempt to archive jobs ended >= 120 days ago, that are currently ended
        $announcementsToArchive = $this->jobAnnouncementRepository->createQueryBuilder('ja')
            ->where('ja.endsOn <= :future AND ja.endsOn IS NOT NULL')
            ->andWhere('ja.status = :endedStatus')
            ->setParameter('future', new DateTime('-120 days', new \DateTimeZone('UTC')))
            ->setParameter('endedStatus', JobAnnouncement::STATUS_ENDED)
            ->getQuery()
            ->getResult();
        $archiveCounter = 0;
        foreach ($announcementsToArchive as $a) {
            /** @var JobAnnouncement $a */
            $status = $this->em->getReference(JobAnnouncementStatus::class, JobAnnouncement::STATUS_ARCHIVED);
            if ($status !== $a->getStatus()) {
                $io->note('Job Announcement ID: '.$a->getId().' should have status '.$status.' but is currently '.$a->getStatus().'. Updating...');
                $archiveCounter++;
                $a->getJobTitle()->setIsVacant(false);
                $a->getJobTitle()->setMarkedVacantBy(null);
            }
            $a->setStatus($status);
            $a->setAssignedTo(null);
            $this->em->persist($a);
        }
        $this->em->flush();

        $io->success('Job Announcements: '.$activateCounter.' Activated. '.$endCounter.' Ended. '.$archiveCounter.' Archived.');
    }
}
