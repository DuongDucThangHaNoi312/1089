<?php

namespace App\Command;

use App\Service\DowngradeSubscriptions;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SubscriptionDowngradeCommand extends Command
{
    protected static $defaultName = 'app:subscription:downgrade';

    private $downgradeSubscriptions;

    public function __construct(?string $name = null, DowngradeSubscriptions $downgradeSubscriptions)
    {
        parent::__construct($name);
        $this->downgradeSubscriptions = $downgradeSubscriptions;
    }

    protected function configure()
    {
        $this
            ->setDescription('This command runs hourly to downgrade City/JobSeeker subscriptions that have sumitted a downgrade request and about to renew')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->comment('Downgrading Subscriptions');
        $this->downgradeSubscriptions->downgradeSubscriptions();
        $io->success('Downgrade requests for today');
    }
}
