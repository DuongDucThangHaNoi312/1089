<?php

namespace App\Form\JobSeeker\Resume;

use App\Entity\User\JobSeekerUser\Resume;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class KeyQualificationsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('yearsWorkedInProfession', null, [
                'label' => 'Total Years Worked in Profession'
            ])
            ->add('yearsWorkedInCityGovernment', null, [
                'label' => 'Total Years Worked in City Govt(s)',
                'required' => false,
            ])
            ->add('education', CollectionType::class, [
                'entry_type' => EducationType::class,
                'entry_options' => [
                    'attr' => ['class' => 'item']
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'by_reference' => false,
                'delete_empty' => true,
                'attr' => ['class' => 'education-collection']
            ])
            ->add('licenseCertifications', CollectionType::class, [
                'entry_type' => LicenseCertificationType::class,
                'entry_options' => [
                    'attr' => ['class' => 'item']
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'by_reference' => false,
                'delete_empty' => true,
                'attr' => ['class' => 'license-certifications-collection']
            ])
            ->add('currentJobTitle')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Resume::class,
            'validation_groups' => array('job_seeker_resume_key_qualifications')
        ]);
    }
}
