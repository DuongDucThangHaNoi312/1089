<?php

namespace App\Form\JobSeeker\Registration;

use App\Entity\User\JobSeekerUser;
use Karser\Recaptcha3Bundle\Form\Recaptcha3Type;
use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StepOneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'label'              => 'Email Address',
                'translation_domain' => 'FOSUserBundle',
                'attr'               => [
                    'placeholder' => 'Enter your email address'
                ]
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type'            => PasswordType::class,
                'label'           => 'Password',
                'options'         => [
                    'translation_domain' => 'FOSUserBundle',
                    'attr'               => [
                        'autocomplete' => 'new-password',
                        'placeholder'  => 'Enter a password'
                    ],
                ],
                'first_options'   => array('label' => 'form.password'),
                'second_options'  => array('label' => 'form.password_confirmation'),
                'invalid_message' => 'fos_user.password.mismatch',
            ])
            ->add('captcha', Recaptcha3Type::class, [
                'constraints' => new Recaptcha3(),
                'action_name' => 'job_seeker_registration_step_one'
            ])
            ->add('continue', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-lg btn-danger mt-5 btn-block'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
            'data_class' => JobSeekerUser::class,
            'csrf_token_id' => 'registration'
        ]);
    }
}
