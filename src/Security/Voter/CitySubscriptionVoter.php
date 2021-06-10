<?php

namespace App\Security\Voter;

use App\Entity\User\CityUser;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class CitySubscriptionVoter extends Voter
{
    private $session;
    private $flashBag;

    public function __construct(SessionInterface $session, FlashBagInterface $flashBag)
    {
        $this->session = $session;
        $this->flashBag = $flashBag;
    }

    protected function supports($attribute, $subject)
    {
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, ['edit_subscription'])
            && $subject instanceof CityUser;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var CityUser $user */
        $user = $token->getUser();
        $city = $user->getCity();
        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case 'edit_subscription':
                // city must own subscription
                if ($user->getCity()->getSubscription() == $subject->getCity()->getSubscription()) {
                    if ($user->getCity()->getIsSuspended()) {
                        $this->session->set('citySuspended', true);
                        $this->session->set('city', $city);
                        $this->flashBag->add('error', 'Sorry, your city has been suspended. Please contact customer service.');
                        return false;
                    }
                    // city must have an active subscription
                    elseif (false == $city->getSubscription()
                        ||
                        ($city->getSubscription()->getExpiresAt() < new \DateTime())
                    ) {
                        $this->session->set('subscriptionExpired', true);
                        $this->flashBag->add('error', 'Your subscription is no longer active!');
                    } elseif ($city->getSubscription() &&  false == $city->getSubscription()->getIsPaid()  && false == $city->getSubscription()->getSubscriptionPlan()->getIsTrial()) {
                        $this->session->set('subscriptionNotPaid', true);
                    }
                    return true;
                }
            break;
        }

        return false;
    }
}
