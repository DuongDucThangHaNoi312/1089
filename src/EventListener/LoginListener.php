<?php

namespace App\EventListener;

use App\Entity\CityRegistration;
use App\Entity\User\CityUser;
use App\Entity\User\JobSeekerUser;
use App\Entity\User\UserLogin;
use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginListener {
    protected $manager;
    protected $router;
    protected $security;
    protected $dispatcher;
    protected $token;
    protected $entityManager;
    protected $flashBag;

    public function __construct(UserManagerInterface $manager, Router $router, AuthorizationChecker $security, EventDispatcherInterface $dispatcher, TokenStorageInterface $token, EntityManagerInterface $entityManager, FlashBagInterface $flashBag)
    {
        $this->manager = $manager;
        $this->router = $router;
        $this->security = $security;
        $this->dispatcher = $dispatcher;
        $this->token = $token;
        $this->entityManager = $entityManager;
        $this->flashBag = $flashBag;

    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event) {
        $this->dispatcher->addListener(KernelEvents::RESPONSE, array($this, 'onKernelResponse'));
    }

    public function onKernelResponse(ResponseEvent $event) {
        // Important: redirect according to user Role

        $request    = $event->getRequest();
        $referer    = $request->server->get('HTTP_REFERER');
        $params     = $referer ? explode('dest_url=', $referer) : null;
        $saveJobUrl = isset($params[1]) ? $params[1] : null;
        $location   = $request->headers->get('location');

        // CIT-917: As a Job Seeker, if I log in AFTER clicking "Save Job Alert" or "Save Job Announcement" the original intent should be remembered and executed
        if($saveJobUrl && strpos($saveJobUrl, '&') !== false) {
            $saveJobUrl = explode('&', $saveJobUrl)[0];
        }

        $parsedUrl = parse_url($location);
        $path = $parsedUrl['path'];
        $route = $this->router->match($path)['_route'];
        if ($this->security->isGranted('ROLE_JOBSEEKER')) {
            /** @var JobSeekerUser $user */
            $user      = $this->token->getToken()->getUser();
            $userLogin = new UserLogin();
            $userLogin->setUser($user);
            $this->entityManager->persist($userLogin);
            $this->entityManager->flush();
            $this->setLoginFrequencyWithUserLogin($user);

            if ($user && $user->getSubscription() && $user->getSubscription()->getIsPaid() == false) {
                $event->setResponse(new RedirectResponse($this->router->generate('job_seeker_subscription')));
            } else {
                if ($route == 'home') {
                    if($saveJobUrl) {
                        $event->setResponse(new RedirectResponse($saveJobUrl));
                    } else {
                        $event->setResponse(new RedirectResponse($this->router->generate('job_seeker_dashboard')));
                    }
                }
            }
        } elseif ($this->security->isGranted('ROLE_CITYUSER')) {
            /** @var CityUser $user */
            $user = $this->token->getToken()->getUser();
            if ($user && $user->getCity() && $user->getCity()->getSubscription() && $user->getCity()->getSubscription()->getIsPaid() == false) {
                $event->setResponse(new RedirectResponse($this->router->generate('city_subscription', ['slug' => $user->getCity()->getSlug()])));
            } else {
                if ($route == 'home') {
                    $event->setResponse(new RedirectResponse($this->router->generate('city_dashboard')));
                }
            }
        } elseif ($this->security->isGranted('ROLE_PENDING_JOBSEEKER')) {
            /** @var JobSeekerUser $user */
            $this->flashBag->add('warning', 'Please complete your Registration before continuing');
            $user = $this->token->getToken()->getUser();
            if ($user->getConfirmationToken() == null) {
                $event->setResponse(new RedirectResponse($this->router->generate("job_seeker_registration_step_two")));
            } else {
                $event->setResponse(new RedirectResponse($this->router->generate("job_seeker_registration_step_one_verify")));
            }
        } elseif ($this->security->isGranted('ROLE_PENDING_CITYUSER')) {
            /** @var CityUser $user */
            $user = $this->token->getToken()->getUser();
            /** If it is a Pending User that has been Invited by an AdminCityUser */
            if (!$this->security->isGranted('ROLE_PENDING_CITYADMIN')) {
                if ($user->getConfirmationToken() == null) {
                    $event->setResponse(new RedirectResponse($this->router->generate("city_registration_step_one_reset_password", [
                        'city_slug' => $user->getCity()->getSlug()
                    ])));
                }
            } elseif (count($user->getCityRegistrations()) > 0 && $user->getConfirmationToken() == null) {
                /** @var CityRegistration $cityRegistration */
                $cityRegistration = $user->getCityRegistrations()[0];
                if ($cityRegistration->getStatus() == null) {
                    $event->setResponse(new RedirectResponse($this->router->generate("city_registration_step_two", [
                        'city_slug' => $cityRegistration->getCity()->getSlug()
                    ])));
                } elseif ($cityRegistration->getStatus() == $this->entityManager->getReference(CityRegistration\Lookup\CityRegistrationStatus::class, CityRegistration::STATUS_PENDING)) {
                    $event->setResponse(new RedirectResponse($this->router->generate("city_registration_awaiting_verification", [
                        'city_slug' => $cityRegistration->getCity()->getSlug()
                    ])));
                }
            }
        } else {
            // could be a user without a Subscription... in which case role checks fail, so check user object type and redirect from there to let custom access decision handler work
            $user = $this->token->getToken()->getUser();
            if ($user instanceof JobSeekerUser) {
                if ($route == 'home') {
                    $event->setResponse(new RedirectResponse($this->router->generate('job_seeker_dashboard')));
                }
            } elseif ($user instanceof CityUser) {
                if ($route == 'home') {
                    $event->setResponse(new RedirectResponse($this->router->generate('city_dashboard')));
                }
            }
        }
    }

    public function setLoginFrequencyWithUserLogin(JobSeekerUser $user)
    {
        // get total User Login
        $userLoginRepo = $this->entityManager->getRepository(UserLogin::class);
        $totalLogin    = $userLoginRepo->getTotalLogin($user);
        //set loginFrequency
        $now            = new \DateTime('now');
        $registeredDays = date_create($now->format('Y-m-d'))->diff(date_create($user->getCreatedAt()->format('Y-m-d')));
        if ($totalLogin) {
            if ($registeredDays->days >= 39) {
                $user->setLoginFrequency((float)($totalLogin['totalLogins'] * 7 / 39));
            } elseif ($registeredDays->days < 7) {
                $user->setLoginFrequency((float)$totalLogin['totalLogins']);
            } else {
                $user->setLoginFrequency((float)($totalLogin['totalLogins'] * 7 / $registeredDays->days));
            }
        }

        $this->entityManager->flush();
    }
}
