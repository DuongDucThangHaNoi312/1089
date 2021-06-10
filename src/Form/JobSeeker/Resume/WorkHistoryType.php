<?php

namespace App\Form\JobSeeker\Resume;

use App\Entity\Resume\LicenseCertification;
use App\Entity\Resume\WorkHistory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WorkHistoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('jobTitle')
            ->add('employerName')
            ->add('startDate', DateType::class, [
                'years' => range(date('Y') - 10, date('Y') + 31),
            ])
            ->add('endDate', DateType::class, [
                'years' => range(date('Y') - 10, date('Y') + 31),
            ])
            ->add('description')
        ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => WorkHistory::class,
            'validation_groups' => array('job_seeker_resume_work_history')
        ]);
    }

    public function getBlockPrefix()
    {
        return 'WorkHistoryType';
    }
}