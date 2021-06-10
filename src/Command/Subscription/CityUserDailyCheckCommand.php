<?php

namespace App\Command\Subscription;

use App\Entity\City\Subscription;
use App\Repository\City\SubscriptionRepository;
use App\Service\SubscriptionManager;
use Exception;
use Psr\Container\ContainerInterface;
use Swift_Mailer;
use Swift_Message;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;
use Twig_Environment;
use Twig_Error_Loader;
use Twig_Error_Runtime;
use Twig_Error_Syntax;

class CityUserDailyCheckCommand extends Command
{
    protected static $defaultName = 'app:subscription:city-user-daily-check';
    private $twig;

    private $subscriptionRepo;

    private $mailer;

    private $container;

    private $fromEmail;

    private $fromEmailName;

    private $subscriptionManager;

    /**
     * JobSeekerDailyCheckCommand constructor.
     * @param SubscriptionManager $subscriptionManager
     * @param Twig_Environment $twig
     * @param SubscriptionRepository $subscriptionRepository
     * @param Swift_Mailer $mailer
     * @param ContainerInterface $container
     * @param null|string $name
     */
    public function __construct(SubscriptionManager $subscriptionManager, Twig_Environment $twig, SubscriptionRepository $subscriptionRepository, Swift_Mailer $mailer, ContainerInterface $container, ?string $name = null)
    {
        parent::__construct($name);

        $this->twig = $twig;
        $this->mailer = $mailer;
        $this->subscriptionRepo = $subscriptionRepository;
        $this->container = $container;
        $this->subscriptionManager = $subscriptionManager;

        $this->fromEmail = $this->container->getParameter('default_from_email');
        $this->fromEmailName = $this->container->getParameter('default_from_name');

    }

    protected function configure()
    {
        $this
            ->setDescription('This command runs daily checks for City subscription status.')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws Throwable
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Runtime
     * @throws Twig_Error_Syntax
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $this->sendTrialExpirationNotices($io);

        $this->cancelOldExpiredTrialSubscriptions($io);

        $this->cancelExpiredSubscriptions($io);

        $io->success('Daily City User subscription check finished.');
    }

    /**
     * For trial subscriptions, we give a 17 day grace period.
     *
     * @param SymfonyStyle $io
     * @throws Exception
     */
    private function cancelOldExpiredTrialSubscriptions(SymfonyStyle $io)
    {
        $trials = $this->subscriptionRepo->getTrialSubscriptionsExpiringOn('-17 days');
        foreach ($trials as $trial) {
            /** @var $trial Subscription */
            $this->subscriptionManager->cancelCitySubscription($trial);
        }
        $io->success(count($trials).' trial subscriptions were cancelled.');

    }

    /**
     * For trial subscriptions, we give a 3 day grace period.
     *
     * @param SymfonyStyle $io
     * @throws Exception
     */
    private function cancelExpiredSubscriptions(SymfonyStyle $io)
    {
        $subscriptions = $this->subscriptionRepo->getSubscriptionsExpiringOn('-3 days');
        foreach ($subscriptions as $subscription) {
            /** @var $trial Subscription */
            $this->subscriptionManager->cancelCitySubscription($subscription);
        }
        $io->success(count($subscriptions).' subscriptions were cancelled.');

    }

    /**
     * @param SymfonyStyle $io
     * @throws Throwable
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Runtime
     * @throws Twig_Error_Syntax
     */
    private function sendTrialExpirationNotices(SymfonyStyle $io)
    {
        $templateName = 'emails/subscription/city_trial_status.html.twig';

        $notificationDays = [14, 7, 0, -7, -14];

        foreach ($notificationDays as $day) {
            $dayString = null;
            $context = null;
            switch ($day) {
                case '14':
                    $dayString = '+14 days';
                    $context = [
                        'subject' => 'Your CityGovJobs.com Trial Subscription Expires in 14 Days',
                        'expiredString' => 'expires in 14 days',
                        'finalWarning' => false
                    ];
                    break;
                case '7':
                    $dayString = '+7 days';
                    $context = [
                        'subject' => 'Your CityGovJobs.com Trial Subscription Expires in 7 Days',
                        'expiredString' => 'expires in 7 days',
                        'finalWarning' => false
                    ];
                    break;
                case '0':
                    $dayString = '+0 days';
                    $context = [
                        'subject' => 'Your CityGovJobs.com Trial Subscription Expires Today',
                        'expiredString' => 'expires today',
                        'finalWarning' => false
                    ];
                    break;
                case '-7':
                    $dayString = '-7 days';
                    $context = [
                        'subject' => 'Your CityGovJobs.com Trial Subscription Expired 7 Days Ago',
                        'expiredString' => 'expired 7 days ago',
                        'finalWarning' => false
                    ];
                    break;
                case '-14':
                    $dayString = '-14 days';
                    $context = [
                        'subject' => 'Your CityGovJobs.com Trial Subscription Expired 14 Days Ago',
                        'expiredString' => 'expired 14 days ago',
                        'finalWarning' => true
                    ];
                    break;
            }

            $trials = $this->subscriptionRepo->getTrialSubscriptionsExpiringOn($dayString);
            foreach ($trials as $trial) {
                /** @var $trial Subscription */
                $context['subscription'] = $trial;
                $context['city'] = $trial->getCity();
                $this->sendEmail($templateName, $context, $trial->getCity()->getAdminCityUser()->getEmail());
            }
            $io->success('There are '.count($trials).' trials expiring in '.$dayString);
        }


    }

    /**
     * @param $templateName
     * @param $context
     * @param $toEmail
     * @throws Throwable
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Runtime
     * @throws Twig_Error_Syntax
     */
    private function sendEmail($templateName, $context, $toEmail)
    {
        $template = $this->twig->load($templateName);
        $subject = $template->renderBlock('subject', $context);
        $textBody = $template->renderBlock('body_text', $context);

        $htmlBody = '';

        if ($template->hasBlock('body_html', $context)) {
            $htmlBody = $template->renderBlock('body_html', $context);
        }

        $message = (new Swift_Message())
            ->setSubject($subject)
            ->setFrom($this->fromEmail, $this->fromEmailName)
            ->setTo($toEmail);

        if (!empty($htmlBody)) {
            $message->setBody($htmlBody, 'text/html')
                ->addPart($textBody, 'text/plain');
        } else {
            $message->setBody($textBody);
        }

        $this->mailer->send($message);
    }
}
