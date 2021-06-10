<?php

namespace App\Form\JobSeeker\Resume;

use App\Entity\Resume\LicenseCertification;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LicenseCertificationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, [
                'label' => false,
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'LicenseCertificationType';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => LicenseCertification::class,
            'validation_groups' => array('job_seeker_resume_key_qualifications')
        ]);
    }
}