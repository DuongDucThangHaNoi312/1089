<?php

namespace App\Security\Voter;

use App\Entity\User\CityUser;
use App\Entity\User\JobSeekerUser;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class SubscriptionStatusVoter extends Voter
{

    private $session;
    private $security;

    public function __construct(SessionInterface $session, Security $security)
    {
        $this->session = $session;
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        return in_array($attribute, ['ROLE_JOBSEEKER', 'ROLE_PENDING_                        JOBSEEKER', 'ROLE_CITYUSER', 'ROLE_USER']);
    }

    /**
     * @param string $attribute
     * @param mixed $subject
     * @param TokenInterface $token
     * @return bool|int
     * @throws \Exception
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        if ($user instanceof JobSeekerUser) {
            /* GLR 2020-05-12... all  users have free basic access now, so not much point in checking subscription stats
               and it's causing issues in edge cases related to trial expiration.
            // user must have an active subscription
            if (false == $user->getSubscription()
                ||
                ($user->getSubscription()->getExpiresAt() < new \DateTime()
                )
            ) {
                $this->session->set('subscriptionExpired', true);
                $this->session->set('subscriptionType', 'jobSeeker');
                return false;

            } elseif ($user->getSubscription() &&  false == $user->getSubscription()->getIsPaid() && false == $user->getSubscription()->getSubscriptionPlan()->getIsTrial()) {
                $this->session->set('subscriptionType', 'jobSeeker');
                $this->session->set('subscriptionNotPaid', true);
                return true;
            } elseif ($user->getSubscription() && $user->getSubscription()->getExpiresAt() >= new \DateTime()) {
                return true;
            }
            */
            return true;
        }

        if ($user instanceof CityUser) {

            $city = $user->getCity();

            // city must not be suspended
            if ($city->getIsSuspended()) {
                $this->session->set('citySuspended', true);
                $this->session->set('city', $city);
                return false;
            }
            // city must have an active subscription
            elseif (false == $city->getSubscription()
                ||
                ($city->getSubscription()->getExpiresAt() < new \DateTime())
            ) {
                $this->session->set('subscriptionExpired', true);
                $this->session->set('subscriptionType', 'city');
                $this->session->set('city', $city);
                return false;
            } elseif ($city->getSubscription() &&  false == $city->getSubscription()->getIsPaid()  && false == $city->getSubscription()->getSubscriptionPlan()->getIsTrial()) {
                $this->session->set('subscriptionType', 'city');
                $this->session->set('city', $city);
                $this->session->set('subscriptionNotPaid', true);
                return true;
            } elseif ($city->getSubscription() && $city->getSubscription()->getExpiresAt() >= new \DateTime()) {
                return true;
            }
        }

        return false;
    }
}
