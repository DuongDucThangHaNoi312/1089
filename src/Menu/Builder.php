<?php

namespace App\Menu;

use App\Entity\City;
use App\Entity\User;
use Knp\Menu\FactoryInterface;
use Symfony\Component\Security\Core\Security;

/**
 * @see https://symfony.com/doc/current/bundles/KnpMenuBundle/index.html
 */
class Builder
{

    private $factory;
    private $security;

    /**
     * Builder constructor.
     * @param FactoryInterface $factory
     * @param Security $security
     */
    public function __construct(FactoryInterface $factory, Security $security)
    {
        $this->factory = $factory;
        $this->security = $security;
    }

    /**
     * @return \Knp\Menu\ItemInterface
     */
    public function createPublicMenu()
    {
        $menu = $this->factory->createItem('root');

        return $menu;
    }

    /**
     * @return \Knp\Menu\ItemInterface
     */
    public function createUserMenu()
    {
        $menu = $this->factory->createItem('root');

        if ($this->security->getToken() && ($this->security->isGranted('ROLE_JOBSEEKER') || $this->security->isGranted('ROLE_PENDING_JOBSEEKER'))) {
            $menu->addChild('Dashboard', ['route' => 'job_seeker_dashboard']);
            $menu->addChild('FAQ', ['route' => 'job_seeker_faq']);
            $menu->addChild('Account', ['route' => 'job_seeker_profile_edit']);
        }
        if ($this->security->getToken() && $this->security->isGranted('ROLE_CITYUSER')) {
            $menu->addChild('Dashboard', ['route' => 'city_dashboard']);
            $menu->addChild('FAQ', ['route' => 'city_faq']);
            $menu->addChild('Account', ['route' => 'city_account_information']);
        }

        if ($this->security->getToken() && $this->security->isGranted('IS_AUTHENTICATED_FULLY')) {
            $menu->addChild('Log Out', ['route' => 'fos_user_security_logout']);
        } else {
            $menu->addChild('Login', ['route' => 'fos_user_security_login'])->setLinkAttribute('class', 'pl-5 pr-5 login btn btn-light btn-rounded-much text-dark');
            $menu->addChild('Post Job', ['route' => 'city_subcription_options'])->setLinkAttribute('class', 'pl-5 pr-5 login btn btn-light btn-rounded-much text-dark');
        }

        foreach ($menu->getChildren() as $child) {
            $child->setLinkAttribute('class', 'px-3');
        }

        return $menu;
    }

    public function createCityUserMenu()
    {
        $menu = $this->factory->createItem('root');

        /** @var City $city */
        $city = $this->security->getUser()->getCity();

        $menu->addChild('Jobs', [
            'route' => 'city_job_announcements',
            'routeParameters' => [
                'slug' => $city->getSlug(),
                'status' => 'to-do'
            ]
        ]);
        $menu->addChild('City Profile', [
            'route' => 'edit_city_profile',
            'routeParameters' => ['slug' => $city->getSlug()]
        ]);
        $menu->addChild('Resumes', ['route' => 'city_resume_search']);
        $menu->addChild('City Links', ['route' => 'city_search']);

        foreach ($menu->getChildren() as $child) {
            $child->setLinkAttribute('class', 'px-3');
        }

        return $menu;
    }

    public function createCityUserResumeMenu() {
        $menu = $this->factory->createItem('root');

        $menu->addChild('Search Resumes', [
            'route' => 'city_resume_search',
        ]);
        $menu->addChild('Saved Resumes', [
            'route' => 'city_resume_saved',
        ]);

        return $menu;
    }

    public function createCityUserJobMenu()
    {
        $menu = $this->factory->createItem('root');

        /** @var City $city */
        $city = $this->security->getUser()->getCity();

        $menuItem = $city->getAllowsJobAnnouncements() ? 'Job Alerts/Announcements' : 'Job Alerts';

        $menu->addChild($menuItem, [
            'route' => 'city_job_announcements',
            'routeParameters' => [
                'slug' => $city->getSlug(),
                'status' => 'to-do'
            ]
        ]);
        $menu->addChild('Manage Job Titles', [
            'route' => 'city_job_titles',
            'routeParameters' => [
                'slug' => $city->getSlug(),
            ]
        ]);
        $menu->addChild('Submitted Interest', [
            'route' => 'city_job_interest',
            'routeParameters' => [
                'slug' => $city->getSlug(),
            ]
        ]);
//        $menu->addChild('Vacancies', [
//            'route' => 'city_job_vacancies',
//            'routeParameters' => [
//                'slug' => $city->getSlug(),
//            ]
//        ]);

        return $menu;
    }

    public function createCityUserJobAnnouncementMenu()
    {
        $menu = $this->factory->createItem('root');

        /** @var City $city */
        $city = $this->security->getUser()->getCity();

        $menu->addChild('Jobs to Post', [
            'route' => 'city_job_announcements',
            'routeParameters' => [
                'slug' => $city->getSlug(),
                'status' => 'to-do'
            ]
        ]);
        $menu->addChild('Active', [
            'route' => 'city_job_announcements',
            'routeParameters' => [
                'slug' => $city->getSlug(),
                'status' => 'active'
            ]
        ]);
        $menu->addChild('Ended', [
            'route' => 'city_job_announcements',
            'routeParameters' => [
                'slug' => $city->getSlug(),
                'status' => 'ended'
            ]
        ]);
        $menu->addChild('Archived', [
            'route' => 'city_job_announcements',
            'routeParameters' => [
                'slug' => $city->getSlug(),
                'status' => 'archived'
            ]
        ]);

        return $menu;
    }

    public function createCityUserJobTitleMenu()
    {
        $menu = $this->factory->createItem('root');

        /** @var City $city */
        $city = $this->security->getUser()->getCity();

        $menu->addChild('Job Titles', [
            'route' => 'city_job_titles',
            'routeParameters' => [
                'slug' => $city->getSlug(),
            ]
        ]);
        $menu->addChild('Departments', [
            'route' => 'city_departments',
            'routeParameters' => [
                'slug' => $city->getSlug(),
            ]
        ]);
        $menu->addChild('Divisions', [
            'route' => 'city_divisions',
            'routeParameters' => [
                'slug' => $city->getSlug(),
            ]
        ]);

        return $menu;

    }

    public function createJobSeekerMenu()
    {
        $menu = $this->factory->createItem('root');

        $menu->addChild('Job Search', [
            'route' => 'job_seeker_jobtitle_search',
            'routeParameters' => [
                '_fragment' => 'announcement'
    ]
        ]);
        $menu->addChild('City Profile & Links Search', ['route' => 'city_search']);

        foreach ($menu->getChildren() as $child) {
            $child->setLinkAttribute('class', 'px-3');
        }

        return $menu;
    }

    public function createCityUserAccountMenu()
    {
        $menu = $this->factory->createItem('root');

        /** @var User\CityUser $user  */
        $user = $this->security->getUser();
        $city = $user->getCity();

        $menu->addChild('Account Information', [
            'route' => 'city_account_information',
        ]);

        if ($city->getAdminCityUser() === $user) {
            $menu->addChild('Subscription', [
                'route' => 'city_subscription',
                'routeParameters' => [
                    'slug' => $user->getCity()->getSlug()
                ]
            ]);

            $menu->addChild('Manage Users', [
                'route' => 'city_manage_users',
                'routeParameters' => [
                    'city_slug' => $user->getCity()->getSlug()
                ]
            ]);
        }

//        $menu->addChild('FAQ', [
//            'route' => 'city_faq'
//        ]);

        $menu->addChild('Change Password', [
            'route' => 'fos_user_change_password',
        ]);
        

        return $menu;

    }

    public function createJobSeekerAccountMenu()
    {
        $menu = $this->factory->createItem('root');

        $user = $this->security->getUser();
        // $city = $user->getCity();

        $menu->addChild('Account Information', [
            'route' => 'job_seeker_profile_edit',
        ]);

        $menu->addChild('Subscription', [
            'route' => 'job_seeker_subscription'
        ]);

//        $menu->addChild('FAQ', [
//            'route' => 'job_seeker_faq'
//        ]);

        $menu->addChild('Change Password', [
            'route' => 'fos_user_change_password',
        ]);
        $menu->addChild('Canncellation', [
            'route' => 'remove_user'
            // 'routeParameters' => [
            //     'id' => $user->getId()
            // ]
        ])
        ->setAttribute('id', 'remove');

//
//        $menu->addChild('Payment History', [
//            'route' => 'job_seeker_payment_history',
//        ]);
//        $menu->addChild('Agreements', [
//            'route' => 'job_seeker_agreements',
//        ]);
//
        return $menu;
    }

    public function createAboutCitiesMenu()
    {
        $menu = $this->factory->createItem('root');

        $menu->addChild('About Cities', [
            'route' => 'about_cities'
        ]);

        $menu->addChild('Job Categories', [
            'route' => 'default_job_categories'
        ]);

        $menu->addChild('Just Starting Out?', [
            'route' => 'just_starting_out'
        ]);

        return $menu;

    }

    /**
     * @return \Knp\Menu\ItemInterface
     */
    public function createFooterMenu()
    {
        $menu = $this->factory->createItem('root');

        /** @var User $user */
        $user = $this->security->getUser();

        $menu->addChild('About Job Alerts', [
            'route' => 'about_job_alerts_job_seekers'
        ]);

        $menu->addChild('About Us', [
            'route' => 'the_team'
        ]);

        if (!($user instanceof User\JobSeekerUser || $user instanceof User\CityUser)) {
            $menu->addChild('General FAQ', [
                'route' => 'faq'
            ]);
        }

        if ($user instanceof User\JobSeekerUser) {
            $menu->addChild('Job Seeker FAQ', [
                'route' => 'job_seeker_faq'
            ]);
        } elseif ($user instanceof User\CityUser) {
            $menu->addChild('City FAQ', [
                'route' => 'city_faq'
            ]);
        }

        $menu->addChild('Contact Us', [
            'route' => 'contact'
        ]);

        $menu->addChild('Privacy Policy', [
            'route' => 'privacy_policy'
        ]);
        $menu->addChild('Terms of Use', [
            'route' => 'terms_of_use'
        ]);

        $menu->addChild('News', [
            'route' => 'news'
        ]);
        return $menu;
    }

    /**
     * @return \Knp\Menu\ItemInterface
     */
    public function createAboutUsMenu()
    {
        $menu = $this->factory->createItem('root');

        $menu->addChild('The Team', [
            'route' => 'the_team'
        ]);

        $menu->addChild('The Founder\'s Story', [
            'route' => 'the_founder_story'
        ]);

        return $menu;
    }

    /**
     * @return \Knp\Menu\ItemInterface
     */
    public function createAboutJobAlertsMenu()
    {
        $menu = $this->factory->createItem('root');

        $menu->addChild('Job Seekers', [
            'route' => 'about_job_alerts_job_seekers'
        ]);

        $menu->addChild('City Governments', [
            'route' => 'about_job_alerts_city_users'
        ]);

        return $menu;
    }

}