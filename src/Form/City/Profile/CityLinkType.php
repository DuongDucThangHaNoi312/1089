<?php

namespace App\Form\City\Profile;

use App\Entity\Url;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CityLinkType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', null, [
                'disabled' => true,
                'form_row_attr' => ['class' => 'col-md-3']
            ])
            ->add('value', null, [
                'label' => 'URL',
                'form_row_attr' => ['class' => 'col-md-9']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Url::class,
        ]);
    }
}
