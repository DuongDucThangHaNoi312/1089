<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\Entity\User;
use App\Entity\User\JobSeekerUser\SavedJobTitle;
use Symfony\Component\Security\Core\Security;

class SubmittedInterestVoter extends Voter {
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

        //only vote on Submitted Interest objects inside this voter
        if (!$subject instanceof User\JobSeekerUser\SubmittedJobTitleInterest) {
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
        /** @var User\JobSeekerUser\SubmittedJobTitleInterest $resume */
        $submittedJobTitleInterest = $subject;

        switch($attribute) {
            case self::EDIT:
                return $this->canEdit($submittedJobTitleInterest, $user);
            case self::VIEW:
                return $this->canView($submittedJobTitleInterest, $user);
        }
    }

    private function canEdit(User\JobSeekerUser\SubmittedJobTitleInterest $submittedJobTitleInterest, User $user)
    {
        return $user === $submittedJobTitleInterest->getJobSeekerUser();
    }

    private function canView(User\JobSeekerUser\SubmittedJobTitleInterest $submittedJobTitleInterest, User $user) {
        return $this->canEdit($submittedJobTitleInterest, $user);
    }
}
