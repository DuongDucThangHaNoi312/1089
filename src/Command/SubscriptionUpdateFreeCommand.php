<?php

namespace App\Command;

use App\Service\UpdateFreeSubscriptions;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SubscriptionUpdateFreeCommand extends Command
{
    protected static $defaultName = 'app:subscription:update:free';

    private $updateFreeSubscriptions;

    public function __construct(?string $name = null, UpdateFreeSubscriptions $updateFreeSubscriptions)
    {
        parent::__construct($name);
        $this->updateFreeSubscriptions = $updateFreeSubscriptions;
    }

    protected function configure()
    {
        $this
            ->setDescription('This command runs daily to update City/JobSeeker subscriptions that have never been associated in Stripe');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->comment('Renewing City Subscriptions');
        $this->updateFreeSubscriptions->renewCitySubscriptions();
        $io->comment('Renewing Job Seeker Subscriptions');
        $this->updateFreeSubscriptions->renewJobSeekerSubscriptions();

        $io->comment('Cancelling City Subscriptions');
        $this->updateFreeSubscriptions->cancelCitySubscriptions();
        $io->comment('Cancelling Job Seeker Subscriptions');
        $this->updateFreeSubscriptions->cancelJobSeekerSubscriptions();

        $io->success('Daily Free Subscription Update has finished.');
    }
}
