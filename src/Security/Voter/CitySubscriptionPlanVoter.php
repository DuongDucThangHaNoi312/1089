<?php

namespace App\Security\Voter;

use App\Entity\SubscriptionPlan\CitySubscriptionPlan;
use App\Entity\User\CityUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class CitySubscriptionPlanVoter extends Voter
{
    protected function supports($attribute, $subject)
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, ['activate', 'reactivate'])
            && $subject instanceof CitySubscriptionPlan;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // must be a City User to have a Job Seeker Subscription Plan
        if ($user instanceof CityUser) {

            if ('reactivate' == $attribute) {
                // can only reactivate the current plan that was cancelled
                if ($user->getCity()->getSubscription()->getSubscriptionPlan() == $subject) {
                    return true;
                }
            } elseif ('activate' == $attribute) {
                return true;
            }
        }

        return false;
    }
}
