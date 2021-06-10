<?php

namespace App\Form\City;

use App\Entity\City\JobTitle;
use App\Entity\JobTitle\Lookup\JobTitleName;
use App\Repository\City\DepartmentRepository;
use App\Repository\City\DivisionRepository;
use App\Repository\JobTitle\Lookup\JobCategoryRepository;
use App\Repository\JobTitle\Lookup\JobTitleNameRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JobTitleType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $city = $options['city'];

        $builder
            ->add('name', TextType::class, [
                'attr' => ['placeholder' => 'Enter Job Title Name'],
                'mapped' => false,
                'data' => $options['jobTitleNameString']
            ])
            ->add('department', null, [
                'placeholder'  => 'Select Department',
                'query_builder' => function (DepartmentRepository $dr) use (&$city) {
                    return $dr->createQueryBuilder('d')
                        ->where('d.city = :city')
                        ->setParameter('city', $city)
                        ->orderBy('d.name');
                }
            ])
            ->add('division', null, [
                'query_builder' => function (DivisionRepository $dr) use (&$city) {
                  return $dr->createQueryBuilder('d')
                    ->where('d.city = :city')
                    ->setParameter('city', $city)
                    ->orderBy('d.name');
                },
                'attr' => ['placeholder'  => 'Type Division, if any']
            ])
            ->add('type', null, [
                'placeholder'  => 'Select Job Type'
            ])
            ->add('isClosedPromotional')
            ->add('Save', SubmitType::class, [
                'label' => 'Save Job Title'
            ])
        ;

        /** @var JobTitle $jobTitle */
        $jobTitle = $builder->getData();
        if ($jobTitle && $jobTitle->getId()) {
            $builder
                ->add('category', null, [
                    'placeholder' => 'Select Category',
                    'query_builder' => function (JobCategoryRepository $jcr) {
                        return $jcr->createQueryBuilder('c')
                            ->orderBy('c.name');
                    }
                ]);

            $builder->add('level_unmapped', TextType::class, [
                'mapped' => false,
                'label' => 'Level',
                'data' => $jobTitle->getLevel() ? $jobTitle->getLevel()->getName() : null ,
                'attr' => [
                    'readonly' => true,
                ]
            ]);

// GLR 2019-10-14 Client decided job level should never be changed by City User
//        $builder
//            ->add('level', null, [
//                'placeholder' => 'Select Level (see definitions)'
//            ]);
            
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => JobTitle::class,
            'validation_groups' => ['job_title_creation']
        ]);
        $resolver->setRequired('city');
        $resolver->setDefault('jobTitleNameString', 'null');
    }
}
