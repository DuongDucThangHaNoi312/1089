<?php

namespace App\Command;

use App\Entity\Configuration;
use App\Entity\User\JobSeekerUser;
use App\EventListener\LoginListener;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class JobSeekerLoginFrequencyCommand extends Command
{
    protected static $defaultName = 'app:job-seeker-user:login-frequency';

    /**
     * @var LoginListener
     */
    private $loginListener;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;


    public function __construct(
        string $name = null,
        LoginListener $loginListener,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct($name);
        $this->loginListener = $loginListener;
        $this->entityManager = $entityManager;

    }

    protected function configure()
    {
        $this->setDescription('This command runs daily to update Login Frequency for job seeker user');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $now = new \DateTime('now');
        /** @var Configuration $configuration */
        $configuration = $this->entityManager->getRepository(Configuration::class)->findOneBySlug(Configuration::CONFIGURATION_LOGIN_FREQUENCY_DATETIME_SLUG);
        if ( ! $configuration) {
            $configuration = new Configuration();
            $configuration->setName(Configuration::CONFIGURATION_LOGIN_FREQUENCY_DATETIME_NAME);
            $configuration->setSlug(Configuration::CONFIGURATION_LOGIN_FREQUENCY_DATETIME_SLUG);
            $configuration->setValue($now);
            $this->entityManager->persist($configuration);
            $this->setLoginFrequency($io);
        } elseif ($configuration->getValue()->format('Y-m-d') !== $now->format('Y-m-d')) {
            $configuration->setValue($now);
            $this->setLoginFrequency($io);
        }

        $this->entityManager->flush();

        $io->success('Daily Login Frequency Update has finished.');
    }


    private function setLoginFrequency($io)
    {
        $jsRepo = $this->entityManager->getRepository(JobSeekerUser::class);
        $jobSeekers = $jsRepo->findAll();

        /** @var JobSeekerUser $jobSeeker */
        foreach ($jobSeekers as $jobSeeker) {
            $this->loginListener->setLoginFrequencyWithUserLogin($jobSeeker);
            $io->comment('Updated Login Frequency for: ' . $jobSeeker->getEmail());
        }
    }
}
