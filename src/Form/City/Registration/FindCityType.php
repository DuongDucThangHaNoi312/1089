<?php

namespace App\Form\City\Registration;

use App\Service\CityChoiceGenerator;
use Karser\Recaptcha3Bundle\Form\Recaptcha3Type;
use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FindCityType extends AbstractType
{
    /** @var CityChoiceGenerator $cityChoiceGenerator */
    protected $cityChoiceGenerator;


    public function __construct(CityChoiceGenerator $cityChoiceGenerator) {
        $this->cityChoiceGenerator = $cityChoiceGenerator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('captcha', Recaptcha3Type::class, [
                'constraints' => new Recaptcha3(),
                'action_name' => 'city_registration_find_city'
            ])
            ->add('city', ChoiceType::class, [
                'required'    => false,
                'mapped'      => false,
                'placeholder' => 'Enter your city',
                'attr'        => [
                    'class'   => 'city-county-state-select2',
                    'data-city-registration' => 'true'
                ]
            ])
//            ->add('captcha', Recaptcha3Type::class, [
//                'constraints' => new Recaptcha3(),
//            ])
            ->add('next', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-lg btn-outline-primary mt-5 btn-block'
                ]
            ])
        ;

        $builder->get('city')->resetViewTransformers();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
