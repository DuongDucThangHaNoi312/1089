<?php

namespace App\Security\Voter;

use App\Entity\User\JobSeekerUser;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class JobSeekerSubscriptionVoter extends Voter
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
            && $subject instanceof JobSeekerUser;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var JobSeekerUser $user */
        $user = $token->getUser();

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case 'edit_subscription':
                // user must own subscription
                if ($user->getSubscription() == $subject->getSubscription()) {
                    // user must have an active subscription
                    if ($user->getSubscription()->getExpiresAt() < new \DateTime()) {
                        $this->session->set('subscriptionExpired', true);
                        //$this->flashBag->add('error', 'Your subscription is no longer active!');

                    } elseif ($user->getSubscription() &&  false == $user->getSubscription()->getIsPaid() && false == $user->getSubscription()->getSubscriptionPlan()->getIsTrial()) {
                        $this->session->set('subscriptionNotPaid', true);
                    }
                    return true;
                }
            break;
        }

        return false;
    }
}
