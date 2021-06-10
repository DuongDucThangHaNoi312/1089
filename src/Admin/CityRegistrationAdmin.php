<?php

namespace App\Admin;

use App\Entity\City;
use App\Entity\CityCityUser;
use App\Entity\CityRegistration;
use App\Entity\SubscriptionPlan\CitySubscriptionPlan;
use App\Service\SubscriptionManager;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Filter\ModelAutocompleteFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class CityRegistrationAdmin extends AbstractAdmin
{

    protected $datagridValues = array (
        'status' => array ('value' => 1)
    );


    protected function configureRoutes(RouteCollection $collection)
    {
        parent::configureRoutes($collection);
        $collection->remove('show');
        $collection->remove('create');
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('status', null, [
                'show_filter' => true
            ])
            ->add('city', ModelAutocompleteFilter::class, [], null, [
                'property' => 'name',
                'to_string_callback' => function($entity) {
                    return $entity->getCityAndState();
                }
            ])
            ->add('cityUser', ModelAutocompleteFilter::class, [], null, [
                'property' => 'lastname'
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('cityUser.firstname', null, [
                'label' => 'First Name'
            ])
            ->add('cityUser.lastname', null, [
                'label' => 'Last Name'
            ])
            ->add('cityUser.email', null, [
                'label' => 'Email'
            ])
            ->add('cityUser.phone', null, [
                'label' => 'Phone'
            ])
            ->add('city')
            ->add('status')
            ->add('decisionDate')
            ->add('createdAt')
            ->add('updatedAt')
            ->add('updatedBy')
            ->add('_action', null, [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('City Info Match Verification', [
                'class' => 'col-md-12',
                'description' => 'Please verify that CGJ city info matches applicant input info.'
            ])
                ->add('city.address', null, [
                    'disabled' => true,
                    'required' => false,
                    'label' => 'CGJ City Address'
                ], [
                    'custom_field_size' => 3
                ])
                ->add('city.zipCode', null, [
                    'disabled' => true,
                    'required' => false,
                    'label' => 'CGJ City Zip Code'
                ], [
                    'custom_field_size' => 2
                ])
                ->add('city.cityHallPhone', null, [
                    'disabled' => true,
                    'required' => false,
                    'label' => 'CGJ City Hall Phone'
                ], [
                    'custom_field_size' => 2
                ])
                ->add('city.timezone', null, [
                    'disabled' => true,
                    'required' => false,
                    'label' => 'CGJ City Timezone'
                ], [
                    'custom_field_size' => 2
                ])
                ->add('city.mainWebsite', null, [
                    'disabled' => true,
                    'required' => false,
                    'label' => 'CGJ City Main Website'
                ], [
                    'custom_field_size' => 3
                ])
                ->add('cityHallAddress', null, [
                    'disabled' => true,
                    'required' => false,
                    'label' => 'Applicant Input Address'
                ], [
                    'custom_field_size' => 3
                ])
                ->add('cityHallZip', null, [
                    'disabled' => true,
                    'required' => false,
                    'label' => 'Applicant Input Zip Code'
                ], [
                    'custom_field_size' => 2
                ])
                ->add('cityHallMainPhone', null, [
                    'disabled' => true,
                    'required' => false,
                    'label' => 'Applicant Input Phone'
                ], [
                    'custom_field_size' => 2
                ])
                ->add('cityTimezone', null, [
                    'disabled' => true,
                    'required' => false,
                    'label' => 'Applicant Input Timezone'
                ], [
                    'custom_field_size' => 2
                ])
                ->add('cityWebsite', null, [
                    'disabled' => true,
                    'required' => false,
                    'label' => 'Applicant Input Website'
                ], [
                    'custom_field_size' => 3
                ])
                ->add('cityInformationMatch', ChoiceType::class, [
                    'label' => 'Does the city information match?',
                    'choices' => [
                        'Yes' => true,
                        'No' => false
                    ],
                    'placeholder' => 'Select Yes or No',
                    'required' => true,
                    'disabled' => $this->getSubject()->getDecisionSent() ? true : false
                ], [
                    'custom_field_size' => 3
                ])
            ->end()
            ->with('Employment Verification', [
                'class' => 'col-md-12',
                'description' => 'Please call city hall and ask for the registrant by name.'
            ])
                ->add('cityUser.firstname', null, [
                    'disabled' => true,
                    'required' => false,
                    'label' => 'Applicant First Name'
                ], [
                    'custom_field_size' => 3
                ])
                ->add('cityUser.lastname', null, [
                    'disabled' => true,
                    'required' => false,
                    'label' => 'Applicant Last Name'
                ], [
                    'custom_field_size' => 3
                ])
                ->add('jobTitle', null, [
                    'disabled' => true,
                    'required' => false,
                    'label' => ' Job Title'
                ], [
                    'custom_field_size' => 3
                ])
                ->add('department', null, [
                    'disabled' => true,
                    'required' => false,
                    'label' => 'Applicant Department'
                ], [
                    'custom_field_size' => 3
                ])
                ->add('applicantWorkForCity', ChoiceType::class, [
                    'label' => 'Does the applicant work for the city?',
                    'choices' => [
                        'Yes' => true,
                        'No' => false
                    ],
                    'placeholder' => 'Select Yes or No',
                    'required' => true,
                    'disabled' => $this->getSubject()->getDecisionSent() ? true : false
                ], [
                    'custom_field_size' => 3
                ])
            ->end()
            ->with('Personal Verification', [
                'class' => 'col-md-12',
                'description' => 'After being transferred to speak to Applicant, 
                    verify you are speaking with Applicant by saying: "Hello, I am [your_name]
                    and am calling from City Gov Jobs.com. I need to verify some information 
                    you submitted in order to approve your application to use City Gov Jobs.com 
                    for job posting. Do you have time to answer a few questions?"'
            ])
                ->add('appliedToUseSystem', ChoiceType::class, [
                    'label' => 'Your name is '.$this->getSubject()->getCityUser()->getFirstname()
                        .' '.$this->getSubject()->getCityUser()->getLastname().' and you applied
                        to use City Gov Jobs.com, correct?',
                    'choices' => [
                        'Yes' => true,
                        'No' => false
                    ],
                    'placeholder' => 'Select Yes or No',
                    'required' => true,
                    'disabled' => $this->getSubject()->getDecisionSent() ? true : false
                ], [
                    'custom_field_size' => 4
                ])
                ->add('workForDepartmentWithJobTitle', ChoiceType::class, [
                    'label' => 'You work for '.$this->getSubject()->getCityUser()->getDepartment()
                        .' and your job title is '.$this->getSubject()->getCityUser()->getJobTitle()
                        .' correct?',
                    'choices' => [
                        'Yes' => true,
                        'No' => false
                    ],
                    'placeholder' => 'Select Yes or No',
                    'required' => true,
                    'disabled' => $this->getSubject()->getDecisionSent() ? true : false
                ], [
                    'custom_field_size' => 4
                ])
                ->add('responsibleToAdvertiseJobOpeningsForCity', ChoiceType::class, [
                    'label' => 'You are responsible for, or can authorize advertising of job openings 
                    for your City, correct?',
                    'choices' => [
                        'Yes' => true,
                        'No' => false
                    ],
                    'placeholder' => 'Select Yes or No',
                    'required' => true,
                    'disabled' => $this->getSubject()->getDecisionSent() ? true : false
                ], [
                    'custom_field_size' => 4
                ])
            ->end()
            ->with('Contact Verification', [
                'class' => 'col-md-12',
                'description' => 'Lastly, would you please verify your direct 
                telephone number and email address for me?'
            ])
                ->add('cityUser.phone', null, [
                    'disabled' => true,
                    'label' => 'Phone'
                ], [
                    'custom_field_size' => 6
                ])
                ->add('cityUser.email', null, [
                    'disabled' => true,
                    'label' => 'Email'
                ], [
                    'custom_field_size' => 6
                ])
                ->add('telephoneAndEmailMatch', ChoiceType::class, [
                    'label' => 'Does above match what applicant reports verbally?',
                    'choices' => [
                        'Yes' => true,
                        'No' => false
                    ],
                    'placeholder' => 'Select Yes or No',
                    'required' => true,
                    'disabled' => $this->getSubject()->getDecisionSent() ? true : false
            ], [
                'custom_field_size' => 3
            ])
            ->end()
            ->with('Final Decision', [
                'class' => 'col-md-12',
                'description' => 'If all above items are "Yes"?, then you may approve. 
                Please note in the box below how such info was verified, by phone, website, etc. 
                If any of the above items is "No"? provide explanations below and Reject application.'
            ])
                ->add('explanation')
                ->add('status', null, [
                    'disabled' => $this->getSubject()->getDecisionSent() ? true : false
                ])
            ->end()
        ;
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('cityUser')
            ->add('city')
            ->add('status')
            ->add('passcode')
            ->add('cityHallAddress')
            ->add('cityHallZip')
            ->add('cityHallMainPhone')
            ->add('cityWebsite')
            ->add('cityTimezone')

        ;
    }

    /**
     * @param $object CityRegistration
     * @throws \Exception
     */
    public function preUpdate($object)
    {

        if (false == $object->getDecisionSent()) {

            // only take special action is status is Rejected or Approved

            $template = null;
            $subject = null;

            if ($object->getStatus()->getSlug() == 'rejected') {
                $subject = 'Your CityGovJobs.com City Registration Was Not Accepted';
                $template = 'emails/city_registration_rejected.html.twig';
            } elseif ($object->getStatus()->getSlug() == 'approved') {
                $subject = 'Your CityGovJobs.com City Registration Was Accepted!';
                $template = 'emails/city_registration_accepted.html.twig';

                $cityCityUser = new CityCityUser();
                $cityCityUser->setCityUser($object->getCityUser());
                $object->getCity()
                    // set currentStars to 5 for city is registered
                    ->setCurrentStars(City::MAX_STARS)
                    ->setIsRegistered(true)
                    ->setIsValidated(true)
                    ->setAdminCityUser($object->getCityUser())
                    ->addCityCityUser($cityCityUser);
                $object->getCityUser()->setCity($object->getCity());
                $object->getCityUser()->setRoles(['ROLE_CITYUSER', 'ROLE_CITYADMIN']);

                $container = $this->getConfigurationPool()->getContainer();
                $subscriptionManager = $container->get('app.subscription_manager');
                $citySubscriptionPlan = $container->get('doctrine')->getRepository(CitySubscriptionPlan::class)->find(CitySubscriptionPlan::CITY_TRIAL_PLAN_ID);
                $subscriptionManager->subscribeCity($object->getCity(), $citySubscriptionPlan, true);
            }

            if ($object->getStatus()->getSlug() != 'pending') {
                $body = $this->getConfigurationPool()->getContainer()->get('templating')->render($template, array('cityRegistration' => $object));
                $message = (new \Swift_Message($subject))
                    ->setFrom('no-reply@citygovjobs.com')
                    ->setTo($object->getCityUser()->getEmail())
                    ->setBody($body,'text/html')
                ;
                $mailer = $this->getConfigurationPool()->getContainer()->get('mailer');
                $mailer->send($message);
                $object->setDecisionSent(true);
                $object->setDecisionDate(new \DateTime('now'));
            }

        }

    }
}
