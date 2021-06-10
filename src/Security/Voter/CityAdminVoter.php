<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\Entity\City;
use App\Entity\User;
use Symfony\Component\Security\Core\Security;

class CityAdminVoter extends Voter {
    const MANAGEUSERS = 'manage-users';

    private $security;

    /**
     * CityVoter constructor.
     * @param Security $security
     */
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, array(self::MANAGEUSERS))) {
            return false;
        }

        // only vote on City objects inside this voter
        if (!$subject instanceof City && !$subject instanceof User\CityUser) {
            return false;
        }

        return true;
    }

    /**
     * @param $attribute
     * @param $subject
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        if (!$user instanceof User\CityUser) {
            return false;
        }

        // super admins can do everything
        if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
            return true;
        }

        // CityAdmins can only manage CityUsers and City they are a part of.
        if ( $this->security->isGranted('ROLE_CITYADMIN')) {
            if ($subject instanceof City) {
                return $subject === $user->getCity();
            }

            if ($subject instanceof User\CityUser) {
                return $subject->getCity() === $user->getCity();
            }
            return false;
        }

        return false;
    }

}