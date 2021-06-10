<?php

namespace App\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormRowTypeExtension extends AbstractTypeExtension {

    /**
     * Return the class of the type being extended.
     */
    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['form_row_attr' => []]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['form_row_attr'] = $options['form_row_attr'];
    }
}

