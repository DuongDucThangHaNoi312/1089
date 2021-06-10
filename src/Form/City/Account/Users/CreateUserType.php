<?php

namespace App\Form\City\Account\Users;

use App\Entity\User\CityUser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateUserType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email Address',
                'translation_domain' => 'FOSUserBundle',
                'attr' => [
                    'placeholder' => 'Enter User\'s email address'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
            'data_class' => CityUser::class,
            'csrf_token_id' => 'invite'
        ]);
    }
}