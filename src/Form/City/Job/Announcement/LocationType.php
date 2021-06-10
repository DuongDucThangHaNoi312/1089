<?php

namespace App\Form\City\Job\Announcement;

use App\Entity\City;
use App\Entity\JobAnnouncement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

class LocationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('street', null, [
                'label' => 'Street Address'
            ])
            ->add('city', Select2EntityType::class, [
                'label'                => 'City, State',
                'class'                => City::class,
                'allow_clear'          => true,
                'remote_route'         => 'search_city',
                'primary_key'          => 'id',
                'multiple'             => false,
                'text_property'        => 'longName',
                'minimum_input_length' => 3,
                'page_limit'           => getenv('PAGE_SIZE'),
                'scroll'               => true,
            ])
            ->add('zipcode', null, [
                'label' => 'Zip Code'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => JobAnnouncement::class,
        ]);
    }
}
