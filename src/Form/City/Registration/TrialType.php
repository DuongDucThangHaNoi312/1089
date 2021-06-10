<?php

namespace App\Form\City\Registration;

use App\Entity\CityRegistration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TrialType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('start', SubmitType::class, [
                'label' => "Start Free 4 month trial",
                'attr' => [
                    'class' => 'btn btn-lg btn-outline-primary mt-5 btn-block'
                ]
            ])
        ;
    }
}
