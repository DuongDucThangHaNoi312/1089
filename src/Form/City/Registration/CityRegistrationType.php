<?php

namespace App\Form\City\Registration;

use App\Entity\CityRegistration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CityRegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('cityHallAddress')
            ->add('cityHallZip')
            ->add('cityHallMainPhone')
            ->add('cityWebsite')
            ->add('cityTimezone')
            ->add('Submit', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-lg btn-outline-primary btn-block mt-5'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CityRegistration::class,
            'validation_groups' => array('registration_verification')
        ]);
    }
}
