<?php

namespace App\Form\City\Job;

use App\Entity\City\Division;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DivisionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, [
                'required' => true,
                'attr' => ['placeholder'  => 'Enter name of Division']
            ])
            ->add('department', null, [
                'required' => true,
                'attr' => ['placeholder' => 'Select linked Department']
            ])
            ->add('Save', SubmitType::class, [
                'label' => 'Save Division'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Division::class,
        ]);
    }
}