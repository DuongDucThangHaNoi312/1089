<?php

namespace App\Service;

use App\Entity\ContactForm;
use App\Entity\User\CityUser;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class EmailHelper
 * @package App\Service
 */
class EmailHelper
{
    /**
     * @var \Swift_Mailer
     */
    protected $mailer;

    /**
     * @var EngineInterface
     */
    protected $twig;

    /**
     * @var TranslatorInterface
     */
    private $translator;


    /**
     * EmailHelper constructor.
     *
     * @param \Swift_Mailer $mailer
     * @param EngineInterface $twig
     * @param TranslatorInterface $translator
     */
    public function __construct(\Swift_Mailer $mailer, EngineInterface $twig, TranslatorInterface $translator)
    {
        $this->mailer     = $mailer;
        $this->twig       = $twig;
        $this->translator = $translator;
    }


    /**
     * @param ContactForm $contactForm
     */
    public function sendContactFormEmail(ContactForm $contactForm)
    {
        $subject = 'CityGovJobs - Contact: ' . $contactForm->getSubject();
        $from    = $contactForm->getEmail();
        $to      = $_ENV['SYSTEM_EMAIL'] ?? 'info@citygovjobs.com';
        $fromName = $contactForm->getName();

        $content = $this->twig->render('emails/contact.html.twig', [
            'contactForm' => $contactForm,
            'subject' => $subject
        ]);

        $message = (new \Swift_Message($subject))
            ->setFrom($from, $fromName)
            ->setTo($to)
            ->setBody(
                $content,
                'text/html'
            );

        $this->mailer->send($message);
    }


    /**
     * @param $to
     * @param $jobSeekerFirstname
     * @param array $jobAnnouncements
     * @param $count
     * @param bool $isSavedSearch
     */
    public function sendActiveJobAlertEmail($to, $jobSeekerFirstname, $jobAnnouncements, $count, $isSavedSearch = true)
    {
        if ($isSavedSearch) {
            $subject  = $this->translator->trans('email.subject.job_alert_for_saved_search_criteria');
            $template = 'emails/notifications/alert_for_saved_search_criteria.html.twig';
        }
        else {
            $subject  = $this->translator->trans('email.subject.job_alert_for_submitted_interest');
            $template = 'emails/notifications/alert_for_submitted_interest.html.twig';
        }

        $from      = getenv('SYSTEM_EMAIL');
        $fromName  = getenv('SYSTEM_EMAIL_NAME');

        $content = $this->twig->render($template, [
            'firstName' => $jobSeekerFirstname,
            'jobs'      => $jobAnnouncements,
            'count'     => $count,
            'max_width' => 600
        ]);

        $message = (new \Swift_Message($subject))
            ->setFrom($from, $fromName)
            ->setTo($to)
            ->setBody($content, 'text/html');

        $this->mailer->send($message);
    }

    /**
     * @param CityUser $cityUser
     * @param $countOfInterestedJobSeekers
     * @param $countOfJobSeekerReceivedNotifications
     * @param $countOfJobAnnouncementClicks
     */
    public function sendCityUserMonthlyReport(CityUser $cityUser, $countOfInterestedJobSeekers, $countOfJobSeekerReceivedNotifications, $countOfJobAnnouncementClicks)
    {
        $subject = $this->translator->trans('email.subject.city_user_monthly_report');
        $from    = getenv('SYSTEM_EMAIL');
        $fromName  = getenv('SYSTEM_EMAIL_NAME');
        $to      = $cityUser->getEmail();

        $content = $this->twig->render('emails/notifications/city_user_monthly_report.html.twig', [
            'user'                                  => $cityUser,
            'countOfInterestedJobSeekers'           => $countOfInterestedJobSeekers,
            'countOfJobSeekerReceivedNotifications' => $countOfJobSeekerReceivedNotifications,
            'countOfJobAnnouncementClicks'          => $countOfJobAnnouncementClicks
        ]);

        $message = (new \Swift_Message($subject))
            ->setFrom($from, $fromName)
            ->setTo($to)
            ->setBody($content, 'text/html');

        $this->mailer->send($message);
    }
}
