<?php

namespace App\Form\City\Account;

use App\Entity\City\Department;
use App\Entity\City\JobTitle;
use App\Entity\User\CityUser;
use App\Repository\City\JobTitleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvents;
use App\Repository\City\DepartmentRepository;
use Symfony\Component\Security\Core\Security;

class AccountInformationType extends AbstractType
{
    private $em;
    private $security;

    public function __construct(EntityManagerInterface $em,
                                Security $security
    )
    {
        $this->security = $security;
        $this->em = $em;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $city = $options['city'];

        $builder
            ->add('firstName', TextType::class, [
                'attr' => [
                    'placeholder' => 'Enter your first name'
                ]
            ])
            ->add('lastName', TextType::class, [
                'attr' => [
                    'placeholder' => 'Enter your last name'
                ]
            ])
            ->add('department', EntityType::class, [
                'class' => Department::class,
                'query_builder' => function (DepartmentRepository $dr) use (&$city) {
                    return $dr->createQueryBuilder('d')
                        ->where('d.city = :city')
                        ->setParameter('city', $city)
                        ->orderBy('d.name');
                },
                'attr' => [
                    'class' => 'js-department'
                ],
                'placeholder' => 'Enter your department'
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'));
        $builder->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'onPreSubmit'));
    }

    protected function addElements(FormInterface $form, $city = null, $department = null) {
        $choicesJobTitles = [];

        if ($department) {
            /** @var JobTitleRepository $jobTitleRepository */
            $jobTitleRepository = $this->em->getRepository(JobTitle::class);
            if ($city) {
                $choicesJobTitles = $jobTitleRepository->findJobTitlesForCityDepartment($city, $department);
            } else {
                $choicesJobTitles = $jobTitleRepository->findJobTitlesForDepartment($department);
            }
        }

        $form
            ->add('jobTitle', EntityType::class, [
                'class' => JobTitle::class,
                'choices' => $choicesJobTitles,
                'attr' => [
                    'class' => 'js-job-titles'
                ],
                'placeholder' => 'Select a department first',
            ])
            ->add('phone', TelType::class, [
                'label' => 'Telephone',
                'attr' => [
                    'placeholder' => 'Enter your phone number',
                    'class' => 'cleave-phone'
                ]
            ])
            ->add('save', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-lg btn-secondary btn-block'
                ]
            ])
        ;
    }

    /**
     * @param FormEvent $event
     */
    function onPreSetData(FormEvent $event) {
        $form = $event->getForm();
        /** @var CityUser $user */
        $user = $this->security->getUser();
        $options = $form->getConfig()->getOptions();
        $city = $options['city'];
        $department = null;
        if ($user->getDepartment()) {
            $department = $user->getDepartment()->getId();
        }

        $this->addElements($form, $city->getId(), $department);
    }

    function onPreSubmit(FormEvent $event) {
        $form = $event->getForm();
        $data = $event->getData();

        $department = isset($data['department']) && $data['department'] ? $data['department'] : null;
        $jobTitles = isset($data['jobTitle']) ? $data['jobTitle'] : null;

        $this->addElements($form, null, $department);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
            'data_class' => CityUser::class,
        ]);
        $resolver->setRequired('city');
    }
}
