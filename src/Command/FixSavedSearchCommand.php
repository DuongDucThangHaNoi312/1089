<?php

namespace App\Command;

use App\Entity\User\JobSeekerUser;
use App\Service\SavedSearchHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class FixSavedSearchCommand extends Command
{
    protected static $defaultName = 'cgj:saved-search:fix';

    private $em;
    private $savedSearchHelper;

    public function __construct(?string $name = null, EntityManagerInterface $em, SavedSearchHelper $savedSearchHelper)
    {
        parent::__construct($name);

        $this->em                = $em;
        $this->savedSearchHelper = $savedSearchHelper;
    }

    protected function configure()
    {
        $this
            ->setDescription('Fix all the default saved searches');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $jobSeekers = $this->em->getRepository(JobSeekerUser::class)->findAll();
        foreach ($jobSeekers as $jobSeeker) {
            $this->savedSearchHelper->saveDefaultSearchCriteria($jobSeeker);
        }


        $io->success('All default saved searches have been updated.');
    }
}
