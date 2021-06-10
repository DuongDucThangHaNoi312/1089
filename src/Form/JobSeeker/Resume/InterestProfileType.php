<?php

namespace App\Form\JobSeeker\Resume;

use App\Entity\City\County;
use App\Entity\JobTitle\Lookup\JobLevel;
use App\Entity\User\JobSeekerUser\Resume;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

class InterestProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('interestedJobType', null, [
                'label' => 'Job Type',
                'attr' => [
                    'placeholder' => 'Select Job Type'
                ]
            ])
            ->add('interestedJobLevels', null, [
            ])
            ->add('interestedJobCategories', null, [
                'label' => 'Job Categories'
            ])
            ->add('interestedJobTitleNames', null, [
                'label' => 'Job Titles of Interest'
            ])
            ->add('interestedCounties', Select2EntityType::class, [
                'class'                => County::class,
                'multiple'             => true,
                'label'                => 'Counties of Interest',
                'remote_route'         => 'search_county',
                'primary_key'          => 'id',
                'text_property'        => 'name',
                'minimum_input_length' => 1,
                'page_limit'           => getenv('PAGE_SIZE'),
                'scroll'               => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Resume::class,
        ]);
    }
}
