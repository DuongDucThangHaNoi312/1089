<?php

namespace App\EventListener;

use App\Entity\User\JobSeekerUser;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Listener responsible to change the redirection at the end of the password changing
 */
class PasswordChangedListener implements EventSubscriberInterface
{
    private $router;

    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;
    }

    public static function getSubscribedEvents()
    {
        return [
            FOSUserEvents::CHANGE_PASSWORD_SUCCESS => 'onPasswordChanged',
        ];
    }

    public function onPasswordChanged(FormEvent $event)
    {
        /** @var $user \FOS\UserBundle\Model\UserInterface */
        $user = $event->getForm()->getData();

        if ($user instanceof JobSeekerUser) {
            $url = $this->router->generate('job_seeker_profile_edit');
        } else {
            $url = $this->router->generate('city_account_information');
        }

        $event->setResponse(new RedirectResponse($url));
    }
}