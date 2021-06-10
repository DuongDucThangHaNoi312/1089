<?php

namespace App\Form;

use App\Entity\City;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CityProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('profileTitle')
            ->add('profileAbout')
            ->add('address')
            ->add('zipCode', null, [
                'label' => 'Zip Code'
            ])
            ->add('cityHallPhone')
            ->add('hoursDescription')
            ->add('hoursDescriptionOther')
            ->add('yearFounded')
            ->add('squareMiles')
            ->add('mainWebsite')
            ->add('sealImage')
            ->add('bannerImage')
            ->add('isRegistered')
            ->add('isValidated')
            ->add('doesCityAllowChanges')
            ->add('counties')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => City::class,
        ]);
    }
}
