<?php

namespace App\Form\City\Job\Announcement;

use App\Entity\JobAnnouncement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IsAlertType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('isAlert', ChoiceType::class, [
                'label' => false,
                'expanded' => true,
                'choices' => [
                    'Alert' => true,
                    'Announcement' => false
                ],
                'choice_attr' => function($choice, $key, $value) {
                    return ['class' => 'is_alert_'.($value ? 'yes' : 'no')];
                },
                'label_attr' => [
                    'class' => 'is_alert_choice_label'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => JobAnnouncement::class,
        ]);
    }
}
