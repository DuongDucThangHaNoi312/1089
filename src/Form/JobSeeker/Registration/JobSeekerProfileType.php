<?php

namespace App\Form\JobSeeker\Registration;

use App\Entity\City\County;
use App\Entity\City\JobTitle;
use App\Entity\JobTitle\Lookup\JobCategory;
use App\Entity\JobTitle\Lookup\JobLevel;
use App\Entity\JobTitle\Lookup\JobTitleName;
use App\Entity\User\JobSeekerUser;
use App\Repository\City\CountyRepository;
use App\Repository\City\JobTitleRepository;
use App\Repository\JobTitle\Lookup\JobTitleNameRepository;
use App\Repository\JobTitle\Lookup\JobCategoryRepository;
use App\Repository\JobTitle\Lookup\JobLevelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Security\Core\Security;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

class JobSeekerProfileType extends AbstractType
{
    private $em;
    private $security;

    public function __construct(EntityManagerInterface $em, Security $security)
    {
        $this->em       = $em;
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        /** @var JobSeekerUser $jobSeeker */
        $jobSeeker = $builder->getData();

        if ($options['step2']) {

            $cityChoices = [];
            $id          = null;
            if ($jobSeeker->getCity() && $jobSeeker->getCounty() && $jobSeeker->getState()) {
                $id                 = $jobSeeker->getCity()->getId() . '_' . $jobSeeker->getCounty()->getId();
                $text               = $jobSeeker->getCity() . ', ' . $jobSeeker->getCounty() . ', ' . $jobSeeker->getState();
                $cityChoices[$text] = $id;
            }

            $builder
                ->add('firstName', TextType::class, [
                    'attr' => [
                        'placeholder' => 'Enter your first name'
                    ]
                ])
                ->add('lastName', TextType::class, [
                    'attr' => [
                        'placeholder' => 'Enter your last name'
                    ]
                ]);
                if ($options['profile']) {
                    $builder
                    ->add('email', EmailType::class, array('label' => 'form.email', 'translation_domain' => 'FOSUserBundle'));
                }

            $builder
                ->add('residentLocation', ChoiceType::class, [
                    'choices'     => $cityChoices,
                    'data'        => $id,
                    'required'    => true,
                    'mapped'      => false,
                    'placeholder' => 'Enter your city',
                    'attr'        => [
                        'class' => 'city-county-state-select2'
                    ]
                ])
                ->add('zipcode', TextType::class, [
                    'label' => 'Zip Code',
                    'attr' => [
                        'placeholder' => 'Enter your zip code'
                    ]
                ]);
            $builder->get('residentLocation')->resetViewTransformers();
        }

        if ($options['step3']) {

            /** @var JobCategoryRepository $jobCategoryRepo */
            $jobCategoryRepo = $this->em->getRepository(JobCategory::class);
            $jobCategoryGenerals    = $jobCategoryRepo->findBy(['isGeneral' => true]);
            $jobCategoryNotGenerals = $jobCategoryRepo->findBy(['isGeneral' => false]);

            $interestedJobTitleExists    = [];
            $jobCategoryNotGeneralExists = '';
            $jobCategoryGeneralExists    = '';

            $choicesJobCategoryNotGenerals  = [];
            $choicesJobCategoryGenerals     = [];
            $interestedJobTitles            = [];

            if ($jobSeeker->getInterestedJobTitleNames()) {
                foreach ($jobSeeker->getInterestedJobTitleNames() as $item) {
                    $interestedJobTitleExists[$item->getName()] = $item->getId();
                }
            }

            if ($jobSeeker->getInterestedJobCategories()) {
                foreach ($jobSeeker->getInterestedJobCategories() as $item) {
                    if ($item->getIsGeneral()) {
                        $jobCategoryGeneralExists = $item->getId();
                    } else {
                        $jobCategoryNotGeneralExists = $item->getId();
                    }
                }
            }

            foreach ($jobCategoryNotGenerals as $item) {
                $choicesJobCategoryNotGenerals[$item->getName()] = $item->getId();
            }

            foreach ($jobCategoryGenerals as $item) {
                $choicesJobCategoryGenerals[$item->getName()] = $item->getId();
            }

            ksort($choicesJobCategoryNotGenerals);
            ksort($choicesJobCategoryGenerals);

            $interestedCounties = $jobSeeker->getInterestedCounties();
            $counties = [];
            foreach ($interestedCounties as $item) {
                $counties[] = $item->getId();
            }

            $jobLevels = [];
            foreach ($jobSeeker->getInterestedJobLevels() as $item) {
                $jobLevels[] = $item->getId();
            }

            if ($counties && $jobLevels) {
                /** @var JobTitleNameRepository $jobTitleNameRepo */
                $jobTitleNameRepo = $this->em->getRepository(JobTitleName::class);
                $jobTitleNames    = $jobTitleNameRepo->findByCountiesAndJobLevel($counties, $jobLevels);

                foreach ($jobTitleNames as $jobTitleName) {
                    $interestedJobTitles[$jobTitleName->getName()] = $jobTitleName->getId();
                }
            }

            $worksForCityChoices = [];
            $id                  = null;
            if ($jobSeeker->getWorksForCity() && $jobSeeker->getWorksForCounty()) {
                $id                         = $jobSeeker->getWorksForCity()->getId() . '_' . $jobSeeker->getWorksForCounty()->getId();
                $text                       = $jobSeeker->getWorksForCity() . ', ' . $jobSeeker->getWorksForCounty() . ', ' . $jobSeeker->getWorksForCounty()->getState();
                $worksForCityChoices[$text] = $id;
            }

            $builder
                ->add('interestedCounties', Select2EntityType::class, [
                    'class'                => County::class,
                    'multiple'             => true,
                    'label'                => 'Select up to 5 counties where you are interested in working',
                    'remote_route'         => 'search_county',
                    'primary_key'          => 'id',
                    'text_property'        => 'name',
                    'minimum_input_length' => 1,
                    'page_limit'           => getenv('PAGE_SIZE'),
                    'placeholder'          => 'Select a county',
                    'scroll'               => true,
                    'required'    => true,
                ])
                ->add('interestedJobType', null, [
                    'label' => 'What type of job are you interested in?',
                    'required'    => true,
                    'attr'  => [
                        'placeholder' => 'Select Job Type'
                    ]
                ])
                ->add('interestedJobLevels', EntityType::class, [
                    'class'    => JobLevel::class,
                    'required' => true,
                    'label'    => 'Select up to 2 job levels you are interested in?',
                    'attr'  => [
                        'class' => 'form-group-check'
                    ],
                    'query_builder' => function(JobLevelRepository $jobLevelRepo) {
                        return $jobLevelRepo->createQueryBuilder('jl')
                                            ->orderBy('jl.id', 'DESC');
                    },
                    'choice_label'  => function($entity) {
                        return $entity->getNameAndDescription();
                    },
                    'multiple' => true,
                    'expanded' => true
                ])
                ->add('interestedJobCategoryGenerals', ChoiceType::class, [
                    'attr'     => [
                        'class' => 'js-job-category'
                    ],
                    'mapped'   => false,
                    'required' => false,
                    'choices'       => $choicesJobCategoryGenerals,
                    'data'          => $jobCategoryGeneralExists
                ])
                ->add('interestedJobCategoryNotGenerals', ChoiceType::class, [
                    'attr'     => [
                        'class' => 'js-job-category'
                    ],
                    'mapped'        => false,
                    'required'      => false,
                    'empty_data' => 'Select a category',
                    'choices'       => $choicesJobCategoryNotGenerals,
                    'data'          => $jobCategoryNotGeneralExists

                ])
                ->add('interestedJobTitleNames', ChoiceType::class, [
                    'required'      => false,
                    'mapped'        => false,
                    'multiple'      => true,
                    'choices'       => $interestedJobTitles,
                    'data'          => $interestedJobTitleExists
                ])
                ->add('worksForCity', ChoiceType::class, [
                    'choices'     => $worksForCityChoices,
                    'data'        => $id,
                    'label'       => 'If you currently work for a City Government in our service area and want to access Closed Promotional jobs at your City, select City name below.',
                    'required'    => false,
                    'mapped'      => false,
                    'attr'        => [
                        'class' => 'city-county-state-select2',
                        'data-placeholder' => 'Select what city you work for, if any.',
                    ]
                ])
                ->add('currentJobTitle', TextType::class, [
                    'label'    => 'If you currently work for a City Government, what is your job title (no abbreviations)?',
                    'required' => false,
                    'attr'     => [
                        'placeholder' => 'Enter your current job title, if any'
                    ]
                ]);

            $builder->get('worksForCity')->resetViewTransformers();
            if (!$interestedJobTitleExists) {
                $builder->get('interestedJobTitleNames')->resetViewTransformers();
            }
        }


        if ($options['step2'] && ! $options['step3'] && ! $options['profile']) {
            $builder->add('next', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-lg btn-outline-primary mt-5 btn-block'
                ]
            ]);
        } elseif ( ! $options['step2'] && $options['step3']) {
            $builder->add('save', SubmitType::class, [
                'label' => 'When Finished, Click Here to Visit Your Dashboard',
                'attr'  => [
                    'class' => 'btn btn-lg btn-danger mt-5 btn-block'
                ]
            ]);
        } else {
            $builder->add('save', SubmitType::class, [
                'label' => 'Save Profile',
                'attr'  => [
                    'class' => 'p-2 mt-5 btn-lg btn btn-primary btn-block'
                ]
            ]);
        }

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
            'data_class'    => JobSeekerUser::class,
            'csrf_token_id' => 'registration',
            'step2'         => true,
            'step3'         => true,
            'profile'       => false,
            'validation_groups' => false
        ]);
    }
}