<?php

namespace App\Mailer;

use App\Entity\CardInterface;
use App\Entity\City;
use App\Entity\CityRegistration;
use App\Entity\User\CityUser;
use App\Entity\User\JobSeekerUser;
use FOS\UserBundle\Mailer\TwigSwiftMailer as BaseTwigSwiftMailer;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TwigSwiftMailer extends BaseTwigSwiftMailer {

    public function sendConfirmationEmailMessage(UserInterface $user)
    {
        $template = $this->parameters['template']['confirmation'];

        $url = $this->router->generate('fos_user_registration_confirm', array('token' => $user->getConfirmationToken()), UrlGeneratorInterface::ABSOLUTE_URL);

        if ($user instanceof CityUser) {
            $url = $this->router->generate('city_registration_step_one_confirm', array('token' => $user->getConfirmationToken()), UrlGeneratorInterface::ABSOLUTE_URL);
        }

        $context = array(
            'user' => $user,
            'confirmationUrl' => $url,
        );

        $this->sendMessage($template, $context, $this->parameters['from_email']['confirmation'], (string) $user->getEmail());
    }

    public function sendInvitationEmailMessage(UserInterface $user, $city_slug, CityUser $invitedBy)
    {
        $template = 'emails/city_user_invitation.html.twig';
        $url = $this->router->generate('fos_user_registration_confirm', array('token' => $user->getConfirmationToken()), UrlGeneratorInterface::ABSOLUTE_URL);

        if ($user instanceof CityUser) {
            $url = $this->router->generate('city_registration_step_one_confirm', array('city_slug' => $city_slug, 'token' => $user->getConfirmationToken()), UrlGeneratorInterface::ABSOLUTE_URL);
        }

        $context = array(
            'user' => $user,
            'confirmationUrl' => $url,
            'invitedBy' => $invitedBy,
            'registeredJobTitle' => $invitedBy->getJobTitle() ? $invitedBy->getJobTitle()->getName() : '',
        );

        $this->sendMessage($template, $context, $this->parameters['from_email']['confirmation'], (string) $user->getEmail());
    }

    public function sendCityRegistrationConfirmationEmailMessage(UserInterface $user, $city_slug) {
        $template = 'emails/fosuser/cityuser/registration_confirmation.html.twig';

        $url = $this->router->generate('fos_user_registration_confirm', array('token' => $user->getConfirmationToken()), UrlGeneratorInterface::ABSOLUTE_URL);

        if ($user instanceof CityUser) {
            $url = $this->router->generate('city_registration_step_one_confirm', array('city_slug' => $city_slug, 'token' => $user->getConfirmationToken()), UrlGeneratorInterface::ABSOLUTE_URL);
        }

        $context = array(
            'user' => $user,
            'confirmationUrl' => $url,
            'city' => $city_slug
        );

        $this->sendMessage($template, $context, $this->parameters['from_email']['confirmation'], (string) $user->getEmail());
    }

    public function sendJobSeekerRegistrationConfirmationEmailMessage(UserInterface $user) {
        $template = $this->parameters['template']['confirmation'];

        $url = $this->router->generate('fos_user_registration_confirm', array('token' => $user->getConfirmationToken()), UrlGeneratorInterface::ABSOLUTE_URL);

        if ($user instanceof JobSeekerUser) {
            $url = $this->router->generate('job_seeker_registration_step_one_confirm', array('token' => $user->getConfirmationToken()), UrlGeneratorInterface::ABSOLUTE_URL);
        }

        $context = array(
            'user' => $user,
            'confirmationUrl' => $url,
        );
        $this->sendMessage($template, $context, $this->parameters['from_email']['confirmation'], (string) $user->getEmail());
    }

    public function sendJobSeekerWelcomeEmailMessage(JobSeekerUser $jobSeekerUser) {
        $template = 'emails/job_seeker_welcome_email.html.twig';
        $context = ['user' => $jobSeekerUser, 'subject' => 'Congratulations! You have completed your Registration!',];
        $this->sendMessage($template, $context, $this->parameters['from_email']['resetting'], (string) $jobSeekerUser->getEmail());

    }

    public function sendCityRegistrationAdminEmailMessage(string $email, CityRegistration $cityRegistration) {
        switch ($cityRegistration->getStatus()->getId()) {
            case CityRegistration::STATUS_PENDING:
                $template = 'emails/city_registration_admin_pending.html.twig';
                break;
            case CityRegistration::STATUS_APPROVED:
                if ($cityRegistration->getPasscode() == null) {
                    $template = 'emails/city_registration_admin_approved.html.twig';
                } else {
                    $template = 'emails/city_registration_admin_passcode_approved.html.twig';
                }
                break;
            default:
                $template = 'emails/city_registration_admin.default.html.twig';
        }
        $url = $this->router->generate("admin_app_cityregistration_edit", ['id' => $cityRegistration->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        $context = [
            'user' => $cityRegistration->getCityUser(),
            'status' => $cityRegistration->getStatus(),
            'city' => $cityRegistration->getCity(),
            'date' => $cityRegistration->getCreatedAt(),
            'url' => $url
        ];

        $this->sendMessage($template, $context, $this->parameters['from_email']['resetting'], (string) $email);
    }

    public function sendPaymentFailureMessage(string $email, $amount, CardInterface $card, int $created, City $city = null) {
        $template = 'emails/subscription/payment_failure_default.html.twig';
        $url = $this->router->generate('job_seeker_subscription', ['update' => 'payment'], UrlGeneratorInterface::ABSOLUTE_URL);
        if ($city) {
            $url = $this->router->generate('city_subscription', ['slug' => $city->getSlug(), 'update' => 'payment'], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        $context = [
            'subject' => 'Action Required -  Your Payment has failed.',
            'link' => $url,
            'amount' => $amount,
            'brand' => $card->getBrand(),
            'last4' => $card->getLast4(),
            'created' => $created,
        ];

        $this->sendMessage($template, $context, $this->parameters['from_email']['resetting'], (string) $email);
    }

    /**
     * @param string $templateName
     * @param array $context
     * @param array $fromEmail
     * @param string $toEmail
     * @throws \Throwable
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    protected function sendMessage($templateName, $context, $fromEmail, $toEmail)
    {
        $template = $this->twig->load($templateName);
        $subject = $template->renderBlock('subject', $context);
        $textBody = $template->renderBlock('body_text', $context);

        $fromName  = getenv('SYSTEM_EMAIL_NAME');

        $htmlBody = '';

        if ($template->hasBlock('body_html', $context)) {
            $htmlBody = $template->renderBlock('body_html', $context);
        }

        $message = (new \Swift_Message())
            ->setSubject($subject)
            ->setFrom($fromEmail, $fromName)
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