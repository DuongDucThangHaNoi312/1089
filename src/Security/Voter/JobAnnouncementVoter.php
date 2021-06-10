<?php

namespace App\Security\Voter;

use App\Entity\JobAnnouncement;
use App\Repository\JobAnnouncementRepository;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\Entity\City;
use App\Entity\User;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\Security\Core\Security;

class JobAnnouncementVoter extends Voter {

    const EDIT = 'edit';
    const VIEW = 'view';

    private $security;
    private $jaRepo;

    /**
     * CityVoter constructor.
     * @param Security $security
     * @param JobAnnouncementRepository $jaRepo
     */
    public function __construct(Security $security, JobAnnouncementRepository $jaRepo)
    {
        $this->security = $security;
        $this->jaRepo = $jaRepo;
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, array(self::EDIT, self::VIEW))) {
            return false;
        }

        // only vote on City objects inside this voter
        if (!$subject instanceof JobAnnouncement) {
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

        // you know $subject is a City object, thanks to supports
        /** @var JobAnnouncement $$jobAnnouncement */
        $jobAnnouncement = $subject;

        switch ($attribute) {
            case self::EDIT:
                return $this->canEdit($jobAnnouncement, $user);
            case self::VIEW:
                return $this->canView($jobAnnouncement, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canEdit(JobAnnouncement $jobAnnouncement, User $user)
    {
        // only city users can edit, and only if they have enough open slots given their subscription type
        $city = $jobAnnouncement->getJobTitle()->getCity();
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('cityUser', $user))
        ;
        if ($city->getCityCityUsers()->matching($criteria)->count()) {
            if ($jobAnnouncement->getStatus()->getId() == JobAnnouncement::STATUS_ACTIVE) {
                return true;
            }

            // CIT-559: in "Posted by CGJ" job alert / announcement should not count against a city's subscription plan limitations
            if ($jobAnnouncement->getIsPostedByCGJ()) {
                return true;
            }

            if (
                $city->getSubscription()->getSubscriptionPlan()->getAllowedActiveJobPostings()
                &&
                $this->jaRepo->getCountActiveJobAnnouncementsForCity($city) >= $city->getSubscription()->getSubscriptionPlan()->getAllowedActiveJobPostings()
            ) {
                return true;
            }
            return true;
        }
        return false;
    }

    private function canView(JobAnnouncement $jobAnnouncement, User $user) {
        // anyone can see active... only city users can view otherwise
        if ($jobAnnouncement->getStatus()->getId() == JobAnnouncement::STATUS_ACTIVE) {
            return true;
        }
        $city = $jobAnnouncement->getJobTitle()->getCity();
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('cityUser', $user))
        ;
        return $city->getCityCityUsers()->matching($criteria)->count();
    }

}