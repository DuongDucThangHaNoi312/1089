<?php

namespace App\Command\Subscription;

use App\Entity\CMSBlock;
use App\Entity\SubscriptionPlan\JobSeekerSubscriptionPlan;
use App\Entity\User\JobSeekerUser\Subscription;
use App\Repository\SubscriptionPlan\JobSeekerSubscriptionPlanRepository;
use App\Repository\User\JobSeekerUser\SubscriptionRepository;
use App\Service\SubscriptionManager;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
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

class JobSeekerDailyCheckCommand extends Command
{
    protected static $defaultName = 'app:subscription:job-seeker-daily-check';

    /**
     * @var Twig_Environment
     */
    private $twig;

    /**
     * @var SubscriptionRepository
     */
    private $subscriptionRepo;

    /**
     * @var Swift_Mailer
     */
    private $mailer;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var
     */
    private $fromEmail;

    /**
     * @var
     */
    private $fromEmailName;

    /**
     * @var SubscriptionManager
     */
    private $subscriptionManager;

    /**
     * @var JobSeekerSubscriptionPlanRepository
     */
    private $jsSubscriptionPlanRepo;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * JobSeekerDailyCheckCommand constructor.
     *
     * @param SubscriptionManager $subscriptionManager
     * @param Twig_Environment $twig
     * @param SubscriptionRepository $subscriptionRepository
     * @param JobSeekerSubscriptionPlanRepository $jsSubscriptionPlanRepo
     * @param Swift_Mailer $mailer
     * @param ContainerInterface $container
     * @param null|string $name
     */
    public function __construct(
        SubscriptionManager $subscriptionManager,
        Twig_Environment $twig,
        SubscriptionRepository $subscriptionRepository,
        JobSeekerSubscriptionPlanRepository $jsSubscriptionPlanRepo,
        Swift_Mailer $mailer,
        ContainerInterface $container,
        EntityManagerInterface $em,
        ?string $name = null)
    {
        parent::__construct($name);

        $this->twig = $twig;
        $this->mailer = $mailer;
        $this->subscriptionRepo = $subscriptionRepository;
        $this->jsSubscriptionPlanRepo = $jsSubscriptionPlanRepo;
        $this->container = $container;
        $this->subscriptionManager = $subscriptionManager;
        $this->em = $em;

        $this->fromEmail = $this->container->getParameter('default_from_email');
        $this->fromEmailName = $this->container->getParameter('default_from_name');

    }

    protected function configure()
    {
        $this
            ->setDescription('This command runs daily checks for Job Seeker subscription status.')
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

        $io->success('Daily job seeker subscription check finished.');
    }

    /**
     *
     * For trial subscriptions, we give a 3 day grace period.
     *
     * @param SymfonyStyle $io
     * @throws Exception
     */
    private function cancelOldExpiredTrialSubscriptions(SymfonyStyle $io)
    {
        /* CIT-919: A Job Seeker's Trial subscription should convert to Free Basic 3 days after expiration */
        $trials = $this->subscriptionRepo->getTrialSubscriptionsExpiringOn('-3 days');

        $jobSeekerBasicSubscriptionPlan = $this->jsSubscriptionPlanRepo->findOneBySlug('basic');

        foreach ($trials as $trial) {
            /** @var $trial Subscription */
            $this->subscriptionManager->cancelJobSeekerSubscription($trial);

            $this->subscriptionManager->subscribeJobSeeker($trial->getJobSeekerUser(), $jobSeekerBasicSubscriptionPlan, true, false);
        }
        $io->success(count($trials).' trial subscriptions were cancelled.');
    }

    /**
     *
     * For normally cancelled subscriptions, we give a 3 day grace period.
     *
     * @param SymfonyStyle $io
     * @throws Exception
     */
    private function cancelExpiredSubscriptions(SymfonyStyle $io)
    {
        $subscriptions = $this->subscriptionRepo->getSubscriptionsExpiringOn('-3 days');
        foreach ($subscriptions as $subscription) {
            /** @var $trial Subscription */
            $this->subscriptionManager->cancelJobSeekerSubscription($subscription);
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
        /*
         * CIT-919: A Job Seeker's Trial subscription should convert to Free Basic 3 days after expiration =>
         * This should have the effect of preventing the "7 days ago" and "14 days ago" emails from sending
         *
         */
        $notificationDays = [7, 0];

        foreach ($notificationDays as $day) {
            $dayString = null;
            $context = null;
            switch ($day) {
                case '14':
                    $dayString = '+14 days';
                    $context = [
                        'subject' => 'Your CityGovJobs.com Trial Subscription Expires in 14 Days',
                        'expiredString' => 'expires in 14 days',
                        'cmsBlockHtml' => 'trial-expires-in-14-days-email-html',
                        'cmsBlockText' => 'trial-expires-in-14-days-email-text',
                        'finalWarning' => false
                    ];
                    break;
                case '7':
                    $dayString = '+7 days';
                    $context = [
                        'subject' => 'Your CityGovJobs.com Trial Subscription Expires in 7 Days',
                        'expiredString' => 'expires in 7 days',
                        'cmsBlockHtml' => 'trial-expires-in-7-days-email-html',
                        'cmsBlockText' => 'trial-expires-in-7-days-email-text',
                        'finalWarning' => false
                    ];
                    break;
                case '0':
                    $dayString = '+0 days';
                    $context = [
                        'subject' => 'Your CityGovJobs.com Trial Subscription Expires Today',
                        'expiredString' => 'expires today',
                        'cmsBlockHtml' => 'trial-expires-today-email-html',
                        'cmsBlockText' => 'trial-expires-today-email-text',
                        'finalWarning' => false
                    ];
                    break;
                case '-7':
                    $dayString = '-7 days';
                    $context = [
                        'subject' => 'Your CityGovJobs.com Trial Subscription Expired 7 Days Ago',
                        'expiredString' => 'expired 7 days ago',
                        'cmsBlockHtml' => 'trial-expired-7-days-ago-email-html',
                        'cmsBlockText' => 'trial-expired-7-days-ago-email-text',
                        'finalWarning' => false
                    ];
                    break;
                case '-14':
                    $dayString = '-14 days';
                    $context = [
                        'subject' => 'Your CityGovJobs.com Trial Subscription Expired 14 Days Ago',
                        'expiredString' => 'expired 14 days ago',
                        'cmsBlockHtml' => 'trial-expired-14-days-ago-email-html',
                        'cmsBlockText' => 'trial-expired-14-days-ago-email-text',
                        'finalWarning' => true
                    ];
                    break;
            }

            $trials = $this->subscriptionRepo->getTrialSubscriptionsExpiringOn($dayString);
            foreach ($trials as $trial) {
                /** @var $trial Subscription */
                $context['subscription'] = $trial;
                $this->sendEmail($context, $trial->getJobSeekerUser()->getEmail());
            }
            $io->success('There are '.count($trials).' trials expiring in '.$dayString);
        }


    }

    /**
     *
     * @param $context
     * @param $toEmail
     * @throws Throwable
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Runtime
     * @throws Twig_Error_Syntax
     */
    private function sendEmail($context, $toEmail)
    {
        $cmsBlockRepo = $this->em->getRepository(CMSBlock::class);

        $htmlBlock = $cmsBlockRepo->findOneBySlug($context['cmsBlockHtml']);
        $context['htmlBlock'] = $htmlBlock ? $htmlBlock->getContent() : null;
        $textBlock = $cmsBlockRepo->findOneBySlug($context['cmsBlockText']);
        $context['textBlock'] = $textBlock ? $textBlock->getContent() : null;

        $template = $this->twig->load('emails/subscription/job_seeker_trial_status.html.twig');
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
