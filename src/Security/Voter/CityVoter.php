<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\Entity\City;
use App\Entity\User;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\Security\Core\Security;

class CityVoter extends Voter {

    const EDIT = 'edit';
    const VIEW = 'view';

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
        if (!in_array($attribute, array(self::EDIT, self::VIEW))) {
            return false;
        }

        // only vote on City objects inside this voter
        if (!$subject instanceof City) {
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

        // super admins can do everything
        if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
            return true;
        }

        // you know $subject is a City object, thanks to supports
        /** @var City $city */
        $city = $subject;

        switch ($attribute) {
            case self::EDIT:
                if ($user instanceOf User) {
                    return $this->canEdit($city, $user);
                }
                return false;
            case self::VIEW:
                if ($user instanceOf User) {
                    return $this->canView();
                }
                return false;
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canEdit(City $city, User $user)
    {
        if (false == $this->security->isGranted('ROLE_CITYUSER')) {
            return false;
        }

        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('cityUser', $user))
            ;
        return $city->getCityCityUsers()->matching($criteria)->count();
    }

    /**
     * @return bool
     */
    private function canView() {
        return true;
    }

}