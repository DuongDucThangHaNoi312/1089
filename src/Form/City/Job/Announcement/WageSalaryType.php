<?php

namespace App\Form\City\Job\Announcement;

use App\Entity\JobAnnouncement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WageSalaryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('wageSalaryLow', MoneyType::class, ['currency' => 'USD'])
            ->add('wageSalaryHigh', MoneyType::class, ['currency' => 'USD'])
            ->add('wageSalaryUnit')
            ->add('wageRangeDependsOnQualifications', null, [
                'label' => 'or Depends on Qualifications (DOQ)'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => JobAnnouncement::class,
            'validation_groups' => ['job_announcement_wage_salary']
        ]);
    }
}
