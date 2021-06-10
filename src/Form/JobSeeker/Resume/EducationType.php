<?php

namespace App\Form\JobSeeker\Resume;

use App\Entity\Resume\Education;
use App\Entity\Resume\Lookup\DegreeType;
use App\Entity\User\JobSeekerUser\Resume;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EducationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('major', null, [
            ])
            ->add('degreeType', EntityType::class, [
                'class' => DegreeType::class,
                'label' => 'Degree',
            ])
        ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Education::class,
            'validation_groups' => array('job_seeker_resume_key_qualifications')
        ]);
    }

    public function getBlockPrefix()
    {
        return 'EducationType';
    }
}