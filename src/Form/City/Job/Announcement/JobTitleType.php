<?php

namespace App\Form\City\Job\Announcement;

use App\Entity\City\JobTitle;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JobTitleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('isClosedPromotional', ChoiceType::class, [
                'label' => "Select one of the following: ",
                'expanded' => true,
                'choices' => [
                    'Open Competitive (Anyone can Apply)' => false,
                    'Closed Promotional (In House Applicants only)' => true,
                ]
            ])
        ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => JobTitle::class,
        ]);
    }
}