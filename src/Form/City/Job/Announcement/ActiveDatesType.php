<?php

namespace App\Form\City\Job\Announcement;

use App\Entity\JobAnnouncement;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActiveDatesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $view_timezone = $options['view_timezone'];
        $builder
            ->add('startsOn', DateTimeType::class, [
                'widget' => 'single_text',
                'model_timezone' => 'UTC',
                'view_timezone' => $view_timezone,
                'required' => true,
                'html5' => true,
                'placeholder' => ' ',
                'attr'   => [
                    'class' => 'ajax-ja-starts-on',
                    'max' => '2050-12-31T23:59'
                ]
            ])
            ->add('endsOn', DateTimeType::class, [
                'widget' => 'single_text',
                'model_timezone' => 'UTC',
                'view_timezone' => $view_timezone,
                'required' => true,
                'html5' => true,
                'placeholder' => ' ',
                'attr'   => [
                    'class' => 'ajax-ja-ends-on',
                    'max' => '2050-12-31T23:59'
                ]
            ])
            ->add('hasNoEndDate', null, [
                'label' => 'Has No End Date',
                'attr'  => [
                    'class' => 'ajax-ja-has-no-end-date'
                ]
            ])
            ->add('endDateDescription', null)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => JobAnnouncement::class,
            'validation_groups' => ['job_announcement_active_dates'],
            'view_timezone' => 'UTC',
        ]);
    }
}
