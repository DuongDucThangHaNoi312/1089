<?php

namespace App\Form\City\Profile;

use App\Entity\City;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AgencyInfoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, [
                'attr' => array(
                    'readonly' => true,
                )
            ])
            ->add('counties')
            ->add('censusPopulations', CollectionType::class, [
                'attr' => ['label' => false],
                'entry_type' => CensusPopulationType::class,
                'entry_options' => [
                    'attr' => ['class' => 'item']
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'by_reference' => false,
                'delete_empty' => true,
                'attr' => ['class' => 'census-population-collection']
            ])
            ->add('squareMiles')
            ->add('countFTE', null, [
                'label' => 'Full Time Equivalent (FTE) Employees'
            ])
            ->add('yearFounded')
            ->add('yearIncorporated')
            ->add('yearChartered')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => City::class,
        ]);
    }
}
