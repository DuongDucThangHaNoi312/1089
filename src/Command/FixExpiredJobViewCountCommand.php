<?php

namespace App\Command;

use App\Entity\JobAnnouncement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class FixExpiredJobViewCountCommand extends Command
{
    protected static $defaultName = 'app:job-annoucement-view:fix';

    private $em;

    public function __construct(?string $name = null, EntityManagerInterface $em)
    {
        parent::__construct($name);
        $this->em          = $em;
    }

    protected function configure()
    {
        $this
            ->setDescription('Delete job view count after job expired or archived.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $offset = 0;
        $count = $limit = 50;

        $jobRepo = $this->em->getRepository(JobAnnouncement::class);
        $jobViewRepo = $this->em->getRepository(JobAnnouncement\View::class);

        while ($count == $limit)
        {
            $jobs = $jobRepo->getEndedOrArchivedJobAnnouncements($offset, $limit);
            $count = count($jobs);
            $offset += $limit;

            /** @var JobAnnouncement $job */
            foreach ($jobs as $job) {
                if ($job->getEndsOn()) {
                    $jobViewRepo->deleteExpiredJobViewCount($job->getId(), $job->getEndsOn());
                }
                $io->write('.');
            }
        }

        $io->success('View count of expired jobs have been deleted.');
    }
}
