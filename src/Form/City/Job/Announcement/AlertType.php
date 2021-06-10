<?php

namespace App\Form\City\Job\Announcement;

use App\Entity\JobAnnouncement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AlertType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('isAlert', HiddenType::class, [
                'data' => true,
            ])
            ->add('applicationUrl', null, [
                'label' => 'Job Alert Link (URL to existing Job Announcement)'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => JobAnnouncement::class,
            'validation_groups' => ['job_announcement_details']
        ]);
    }
}
