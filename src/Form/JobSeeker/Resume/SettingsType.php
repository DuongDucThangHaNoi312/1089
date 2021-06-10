<?php

namespace App\Form\JobSeeker\Resume;

use App\Entity\City;
use App\Entity\User\JobSeekerUser\Resume;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

class SettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('isAvailableForSearch', ChoiceType::class, [
                'choices' => array(
                    'Yes' => true,
                    'No' => false
                ),
                'expanded' => true,
                'label' => 'Do you want to make your resume available to cities through searches?'
            ])
            ->add('citiesToBlock', Select2EntityType::class, [
                'class'                => City::class,
                'multiple'             => true,
                'label'                => 'Select which cities to block (only cities from Active states served by CityGovJobs are eligible to be blocked.)',
                'remote_route'         => 'search_city',
                'primary_key'          => 'id',
                'text_property'        => 'longName',
                'minimum_input_length' => 3,
                'page_limit'           => getenv('PAGE_SIZE'),
                'scroll'               => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Resume::class,
            'validation_groups' => ['job_seeker_resume_settings']
        ]);
    }
}
