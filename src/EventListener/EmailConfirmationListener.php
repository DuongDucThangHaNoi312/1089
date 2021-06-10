<?php

namespace App\EventListener;
use App\Entity\User\CityUser;
use App\Mailer\TwigSwiftMailer;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EmailConfirmationListener implements EventSubscriberInterface {

    /** @var TwigSwiftMailer $mailer */
    private $mailer;
    private $tokenGenerator;
    private $router;
    private $session;
    private $em;

    /**
     * EmailConfirmationListener constructor.
     *
     * @param MailerInterface         $mailer
     * @param TokenGeneratorInterface $tokenGenerator
     * @param UrlGeneratorInterface   $router
     * @param SessionInterface        $session
     */
    public function __construct(MailerInterface $mailer, TokenGeneratorInterface $tokenGenerator, UrlGeneratorInterface $router, SessionInterface $session, EntityManager $em)
    {
        $this->mailer = $mailer;
        $this->tokenGenerator = $tokenGenerator;
        $this->router = $router;
        $this->session = $session;
        $this->em = $em;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            FOSUserEvents::REGISTRATION_SUCCESS => 'onRegistrationSuccess',
        );
    }

    /**
     * @param FormEvent $event
     */
    public function onRegistrationSuccess(FormEvent $event)
    {
        /** @var $user \FOS\UserBundle\Model\UserInterface */
        $user = $event->getForm()->getData();
        $request = $event->getRequest();
        $city_slug =$request->attributes->get('city_slug');

        if ($user instanceof CityUser) {
            $user->setEnabled(false);
        }

        if (null === $user->getConfirmationToken()) {
            $user->setConfirmationToken($this->tokenGenerator->generateToken());
            $this->em->persist($user);
            $this->em->flush();
        }

        if ($user instanceof CityUser) {
            if ($user->hasRole('ROLE_PENDING_CITYADMIN')) {
                $this->mailer->sendCityRegistrationConfirmationEmailMessage($user, $city_slug);
            } else {
                $city = $user->getCity();
                $this->mailer->sendInvitationEmailMessage($user, $city_slug, $city->getAdminCityUser());
            }
            $this->session->set('fos_user_send_confirmation_email/email', $user->getEmail());
            $city = $user->getCity();
            $url = $this->router->generate('city_registration_step_one_verify', ['city_slug' => $city_slug]);
            if ($city) {
                $url = $this->router->generate('city_manage_users', ['city_slug' => $city_slug]);
            }
        } else {
            $this->mailer->sendJobSeekerRegistrationConfirmationEmailMessage($user);
            $this->session->set('fos_user_send_confirmation_email/email', $user->getEmail());
            $route = 'job_seeker_registration_step_one_verify';
            $routeParams = $request->query->all();
            if (array_key_exists('dest_url',$routeParams) && $routeParams['dest_url']) {
                $url = $routeParams['dest_url'];
            } else {
                $url = $this->router->generate($route);
            }
        }
        $event->setResponse(new RedirectResponse($url));
    }
}