<?php

namespace App\Form\City\Profile;

use App\Entity\City;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CensusPopulationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('year', null, [
            ])
            ->add('population', null, [
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'CensusPopulationType';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => City\CensusPopulation::class,
        ]);
    }
}
