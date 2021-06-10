<?php

namespace App\Form\JobSeeker\Resume;

use App\Entity\City;
use App\Entity\User\JobSeekerUser\Resume;
use Sonata\AdminBundle\Form\Type\Filter\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

class ResumeJobSeekerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', null, [
                'label' => 'First Name'
            ])
            ->add('lastname', null, [
                'label'  => 'Last Name'
            ])
            ->add('city', Select2EntityType::class, [
                'label'                => 'City, State',
                'class'                => City::class,
                'remote_route'         => 'search_city',
                'primary_key'          => 'id',
                'multiple'             => false,
                'text_property'        => 'longName',
                'minimum_input_length' => 3,
                'page_limit'           => getenv('PAGE_SIZE'),
                'scroll'               => true,
            ])
            ->add('phone', TelType::class, ['attr' => ['class' => 'cleave-phone']])
            ->add('email')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Resume::class,
            'validation_groups' => array('job_seeker_resume_job_seeker')
        ]);
    }
}
