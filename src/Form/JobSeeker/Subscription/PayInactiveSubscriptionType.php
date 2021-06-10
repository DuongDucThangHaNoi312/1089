<?php

namespace App\Form\JobSeeker\Subscription;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

class PayInactiveSubscriptionType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('subscriptionId', HiddenType::class);
    }
}