<?php

namespace App\Command;

use App\Entity\SubscriptionPlan\JobSeekerSubscriptionPlan;
use App\Entity\User\JobSeekerUser\Subscription;
use App\Repository\User\JobSeekerUser\SubscriptionRepository;
use App\Service\SubscriptionManager;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ConvertTrialToBasicSubscriptionCommand extends Command
{
    protected static $defaultName = 'app:subscription:convert-trial-to-basic';

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var SubscriptionManager
     */
    private $subscriptionManager;

    public function __construct(EntityManagerInterface $em, SubscriptionManager $subscriptionManager, ?string $name = null)
    {
        parent::__construct($name);

        $this->em = $em;
        $this->subscriptionManager = $subscriptionManager;
    }

    protected function configure()
    {
        $this
            ->setDescription('This is one time command, used to convert all Trial Subscription Plan to Basic Plan')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        /*** CONVERT TRIAL PLAN TO BASIC PLAN ***/
        $count = 0;

        /** @var SubscriptionRepository $subRepo */
        $subRepo = $this->em->getRepository(Subscription::class);
        $planRepo = $this->em->getRepository(JobSeekerSubscriptionPlan::class);

        $trialPlan = $planRepo->findOneBySlug('free-trial');
        $basicPlan = $planRepo->findOneBySlug('basic');

        $subscriptions = $subRepo->findBySubscriptionPlan($trialPlan);
        $expiredDate = (new DateTime('now'))->modify('-2 days');
        /** @var Subscription $subscription */
        foreach ($subscriptions as $subscription) {
            if ($subscription->getExpiresAt() < $expiredDate) {
                $jobSeeker = $subscription->getJobSeekerUser();
                $this->subscriptionManager->subscribeJobSeeker($jobSeeker, $basicPlan, true, false);
                $jobSeeker->getSubscription()->setExpiresAt(new DateTime('+1 month', new \DateTimeZone('UTC')));

                $count++;
            }
        }
        $this->em->flush();
        $io->success($count . ' job seekers with Trial Plan have been converted to Basic Plan');

        /*** CONVERT CANCELLED PLAN TO BASIC PLAN ***/
        $count = 0;
        $cancelledSubscriptions = $subRepo->getCancelledSubscriptionPlan();
        /** @var Subscription $cancelledSubscription */
        foreach ($cancelledSubscriptions as $cancelledSubscription) {
            $jobSeeker = $cancelledSubscription->getJobSeekerUser();
            $this->subscriptionManager->subscribeJobSeeker($jobSeeker, $basicPlan, true, false);
            $jobSeeker->getSubscription()->setExpiresAt(new DateTime('+1 month', new \DateTimeZone('UTC')));

            $count++;
        }
        $this->em->flush();
        $io->success($count . ' job seekers with Cancelled Plan have been converted to Basic Plan');

    }
}
