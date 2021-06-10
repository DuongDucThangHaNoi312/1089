<?php

namespace App\Form\JobSeeker\JobAlert;

use App\Entity\User\JobSeekerUser;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JobAlertSettingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('receiveAlertsForSubmittedInterest', ChoiceType::class, [
                'attr'  => [
                    'class' => 'form-group-check'
                ],
                'choices'   => [
                    'No'  => '0',
                    'Yes' => '1'
                ],
                'multiple' => false,
                'expanded' => true,
                'required' => false
            ])
            ->add('receiveAlertsForJobsMatchingSavedSearchCriteria', ChoiceType::class, [
                'attr'  => [
                    'class' => 'form-group-check'
                ],
                'choices'   => [
                    'No'  => '0',
                    'Yes' => '1'
                ],
                'multiple' => false,
                'expanded' => true,
                'required' => false
            ])
            ->add('notificationPreferenceForSubmittedInterest', ChoiceType::class, [
                'attr'  => [
                    'class' => 'notification-select'
                ],
                'label' => 'How often do you want to receive job alerts from Submitted Interest?',
                'choices'  => [
                    'Daily'   => JobSeekerUser::NOTIFICATION_PREFERENCE_DAILY,
                    'Weekly'  => JobSeekerUser::NOTIFICATION_PREFERENCE_WEEKLY,
                    'Monthly' => JobSeekerUser::NOTIFICATION_PREFERENCE_MONTHLY,
                    'None'    => JobSeekerUser::NOTIFICATION_PREFERENCE_NONE
                ],
            ])
            ->add('notificationPreferenceForJobsMatchingSavedSearchCriteria', ChoiceType::class, [
                'attr'  => [
                    'class' => 'notification-select'
                ],
                'label' => 'How often do you want to receive job alerts from Search Criteria?',
                'choices'  => [
                    'Daily'   => JobSeekerUser::NOTIFICATION_PREFERENCE_DAILY,
                    'Weekly'  => JobSeekerUser::NOTIFICATION_PREFERENCE_WEEKLY,
                    'Monthly' => JobSeekerUser::NOTIFICATION_PREFERENCE_MONTHLY,
                    'None'    => JobSeekerUser::NOTIFICATION_PREFERENCE_NONE
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save Changes',
                'attr'  => [
                    'class' => 'p-2 mt-2 btn btn-outline-primary btn-block'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => JobSeekerUser::class,
        ]);
    }
}
