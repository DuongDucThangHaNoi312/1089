<?php

namespace App\Command;

use App\Service\CheckSubscriptionPlanChanges;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SubscriptionPlanUpdatePricingCommand extends Command
{

    protected static $defaultName = 'app:subscription-plan:update-pricing';

    private $checkSubscriptionPlanChanges;

    /**
     * SubscriptionPlanUpdatePricingCommand constructor.
     * @param null|string $name
     * @param CheckSubscriptionPlanChanges $checkSubscriptionPlanChanges
     */
    public function __construct(?string $name = null, CheckSubscriptionPlanChanges $checkSubscriptionPlanChanges)
    {
        parent::__construct($name);
        $this->checkSubscriptionPlanChanges = $checkSubscriptionPlanChanges;
    }

    protected function configure()
    {
        $this
            ->setDescription('Update Subscription Plan pricing and migrate Users to new pricing');
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $this->checkSubscriptionPlanChanges->check();
        $io->success('Subscription Plans Pricing has been updated.');
    }
}
