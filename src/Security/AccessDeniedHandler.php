<?php

namespace App\Security;

use App\Entity\CityRegistration;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;

class AccessDeniedHandler implements AccessDeniedHandlerInterface,AuthenticationSuccessHandlerInterface
{

    private $session;
    private $flashBag;
    private $router;
    private $tokenStorage;
    private $em;

    /**
     * AccessDeniedHandler constructor.
     * @param SessionInterface $session
     * @param FlashBagInterface $flashBag
     * @param RouterInterface $router
     */
    public function __construct(SessionInterface $session, FlashBagInterface $flashBag, RouterInterface $router, TokenStorageInterface $tokenStorage, EntityManagerInterface $em)
    {
        $this->session = $session;
        $this->flashBag = $flashBag;
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
        $this->em = $em;
    }

    /**
     * @param Request $request
     * @param AccessDeniedException $accessDeniedException
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, AccessDeniedException $accessDeniedException)
    {

        $currentRoute = $request->attributes->get('_route');
        if ($this->session->get('subscriptionExpired')) {
            $this->flashBag->add('error', 'Your subscription is no longer active!');
            if ($this->session->get('subscriptionType') == 'jobSeeker') {
                if ($request->get('update')) {
                    return new RedirectResponse($this->router->generate('job_seeker_subscription'));
                }
                return new RedirectResponse($this->router->generate('job_seeker_subscription', ['update' => 'subscription']));
            } elseif ($this->session->get('subscriptionType') == 'city') {
                if ($request->get('update')) {
                    return new RedirectResponse($this->router->generate('city_subscription', ['slug' => $this->session->get('city')->getSlug()]));
                }
                return new RedirectResponse($this->router->generate('city_subscription', ['slug' => $this->session->get('city')->getSlug(), 'update' => 'subscription']));
            } else {
                throw new \LogicException('Code should not reach this point.');
            }
        } elseif ($this->session->get('citySuspended')) {
            $this->flashBag->add('error', 'Sorry, your city has been suspended. Please contact customer service.');
        }

        if ($this->session->get('subscriptionNotPaid')) {
            if ($this->session->get('subscriptionType') == 'jobSeeker') {
                if ($request->get('update')) {
                    return;
                }
                return new RedirectResponse($this->router->generate('job_seeker_subscription', ['update' => 'payment']));
            } elseif($this->session->get('subscriptionType') == 'city') {
                if ($request->get('update')) {
                    return;
                }
                return new RedirectResponse($this->router->generate('city_subscription', ['slug' => $this->session->get('city')->getSlug(), 'update' => 'payment']));
            }
        }
        $user = $this->tokenStorage->getToken()->getUser();
        if ($user instanceof User\JobSeekerUser) {
            if (in_array('ROLE_PENDING_JOBSEEKER', $user->getRoles())) {
                $this->flashBag->add('warning', 'Please complete your Registration before continuing.');
                $url = $this->router->generate('job_seeker_registration_step_two');
                if ($user->getConfirmationToken()) {
                    $url = $this->router->generate('job_seeker_registration_step_one_verify');
                } elseif ($user->getCity() && $user->getState()) {
                    $url = $this->router->generate('job_seeker_registration_step_three');
                }
                return new RedirectResponse($url);
            }
        }

        return new RedirectResponse($this->router->generate('access-denied'));

    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $user = $this->tokenStorage->getToken()->getUser();

        if ($user instanceof User\CityUser) {
            $cityRegistrations = $user->getCityRegistrations();
            $approved = $this->em->getRepository(CityRegistration\Lookup\CityRegistrationStatus::class)->findOneBy(['slug' => 'approved']);
            foreach ($cityRegistrations as $cityRegistration) {
                if ($cityRegistration->getStatus() === $approved) {
                    return new RedirectResponse($this->router->generate('access-denied'));
                }
            }
            $session = new Session();
            $session->invalidate();
            $session->getFlashBag()->add('error', 'Your account has not been approved by the admin!');
        }

        return new RedirectResponse($this->router->generate('access-denied'));
    }
}
