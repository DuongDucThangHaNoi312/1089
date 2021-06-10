<?php

namespace App\DataFixtures\ORM;

use App\Entity\Group;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class UserGroupFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $group = new Group('Limited Admin');
        $group->addRole('ROLE_LIMITED_ADMIN');

        $manager->persist($group);
        $manager->flush();
    }
}
