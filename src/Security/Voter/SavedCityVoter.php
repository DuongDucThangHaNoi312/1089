<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\Entity\User;
use Symfony\Component\Security\Core\Security;

class SavedCityVoter extends Voter {
    const EDIT = 'edit';
    const VIEW = 'view';

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, array(self::EDIT, self::VIEW))) {
            return false;
        }

        //only vote on SavedCities objects inside this voter
        if (!$subject instanceof User\SavedCity) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        // super admins can do everything
        if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
            return true;
        }

        // you know $subject is a Resume, thanks to supports
        /** @var User\SavedCity $resume */
        $savedCity = $subject;

        switch($attribute) {
            case self::EDIT:
                return $this->canEdit($savedCity, $user);
            case self::VIEW:
                return $this->canView($savedCity, $user);
        }
    }

    private function canEdit(User\SavedCity $savedCity, User $user)
    {
        return $user === $savedCity->getUser();
    }

    private function canView(User\SavedCity $savedCity, User $user) {
        return $this->canEdit($savedCity, $user);
    }
}
