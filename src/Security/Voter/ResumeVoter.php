<?php

namespace App\Security\Voter;

use App\Entity\JobTitle\Lookup\JobLevel;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\Entity\User;
use App\Entity\User\JobSeekerUser\Resume;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\Security\Core\Security;

class ResumeVoter extends Voter {
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

        //only vote on Resume objects inside this voter
        if (!$subject instanceof Resume) {
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
        /** @var Resume $resume */
        $resume = $subject;

        switch($attribute) {
            case self::EDIT:
                return $this->canEdit($resume, $user);
            case self::VIEW:
                return $this->canView($resume, $user);
        }
    }

    private function canEdit(Resume $resume, User $user)
    {
        return $user === $resume->getJobSeeker();
    }

    private function canView(Resume $resume, User $user) {
        if ($user instanceof User\CityUser) {
            return true;
        }
        return $this->canEdit($resume, $user);
    }
}
