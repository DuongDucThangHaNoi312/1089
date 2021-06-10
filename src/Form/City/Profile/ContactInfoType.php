<?php

namespace App\Form\City\Profile;

use App\Entity\City;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactInfoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('cityHallPhone', TelType::class, [
                'label' => 'Telephone',
                'attr' => ['class' => 'cleave-phone']
            ])
            ->add('hoursDescription', TextType::class)
            ->add('hoursDescriptionOther', TextType::class, [
                'required' => false,
            ])
            ->add('address')
            ->add('zipCode', null, [
                'label' => 'Zip Code'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => City::class,
        ]);
    }
}
