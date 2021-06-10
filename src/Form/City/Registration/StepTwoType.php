<?php

namespace App\Form\City\Registration;

use App\Entity\City\Department;
use App\Entity\City\JobTitle;
use App\Entity\CityRegistration;
use App\Entity\User\CityUser;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StepTwoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
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
            ])
            ->add('jobTitle', TextType::class, [
                'attr' => [
                    'placeholder' => 'Enter your job title'
                ]
            ])
            ->add('department', TextType::class, [
                'attr' => [
                    'placeholder' => 'Enter your department'
                ]
            ])
            ->add('phone', TelType::class, [
                'attr' => [
                    'placeholder' => 'Enter your phone number',
                    'class' => 'cleave-phone'
                ]
            ])
            ->add('agreeTermsOfUseAgreement',CheckboxType::class,  [
                'required' => true,
                'mapped' => false,
                ])
            ->add('next', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-lg btn-outline-primary mt-5 btn-block'
                ]
            ]);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'validation_groups' => array('registration_step_two')
        ]);
    }
}
