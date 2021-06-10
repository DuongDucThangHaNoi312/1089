<?php

namespace App\Form\JobSeeker\Resume;

use App\Entity\User\JobSeekerUser\Resume;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SummaryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('careerObjective', TextareaType::class, [
                'label' => 'Career Objective/Summary'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Resume::class,
            'validation_groups' => array('job_seeker_resume_summary')
        ]);
    }
}
