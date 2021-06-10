<?php

namespace App\Form\JobSeeker\Resume;

use App\Entity\User\JobSeekerUser\Resume;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WorkHistoriesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('workHistories', CollectionType::class, [
                'entry_type' => WorkHistoryType::class,
                'entry_options' => [
                    'label' => false,
                    'attr' => ['class' => 'item']
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'by_reference' => false,
                'delete_empty' => true,
                'attr' => ['class' => 'work-history-collection']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Resume::class,
            'validation_groups' => array('job_seeker_resume_work_history')
        ]);
    }
}
