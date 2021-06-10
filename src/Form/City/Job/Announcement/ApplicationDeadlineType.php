<?php

namespace App\Form\City\Job\Announcement;

use App\Entity\JobAnnouncement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApplicationDeadlineType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $view_timezone = $options['view_timezone'];
        $builder
            ->add('applicationDeadline', DateTimeType::class, [
                'widget' => 'single_text',
                'model_timezone' => 'UTC',
                'view_timezone' => $view_timezone,
                'required' => false,
                'html5' => true,
                'placeholder' => ' ',
                'attr' => [
                    'max' => '2050-12-31T23:59'
                ]
            ])
            ->add('hasNoEndDate', null, [
                'label' => 'Has No End Date',
                'attr'  => [
                    'class' => 'ajax-ja-deadline-has-no-end-date'
                ]
            ])
            ->add('endDateDescription', null)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'validation_groups' => ['job_announcement_application_deadline'],
            'data_class' => JobAnnouncement::class,
            'view_timezone' => 'UTC',
        ]);
    }
}
