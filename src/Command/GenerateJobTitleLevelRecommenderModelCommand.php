<?php

namespace App\Command;

use App\Service\JobTitleML;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateJobTitleLevelRecommenderModelCommand extends Command
{
    protected static $defaultName = 'app:generate:job-title:models';

    private $recommender;

    public function __construct(JobTitleML $recommender, ?string $name = null)
    {
        parent::__construct($name);
        $this->recommender = $recommender;
    }

    protected function configure()
    {
        $this
            ->setDescription('Generate Job Title Recommender Models for Job Level and Job Category')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        // Generate Job Title Level Model
        $outputDirectory = 'assets/machine_learning/model';
        $this->recommender->generateJobTitleLevelRecommenderModel($outputDirectory);
        $io->success('Job Title Level trained.');

        // Generate Job Title Category Model
        $features = 1;
        $this->recommender->generateJobTitleCategoryRecommenderModel($outputDirectory, $features);
        $io->writeln('');
        $io->success('Job Title Category trained.');

        $io->writeln('');
        $io->note('After generating models please run yarn encore production/dev to place the models in the public directory');
    }
}
