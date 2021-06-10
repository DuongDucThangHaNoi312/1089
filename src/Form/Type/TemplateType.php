<?php
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TemplateType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array( 'template' => null));
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (isset($options['template'])) {
            $view->vars['template'] = $options['template'];
        }
    }

    public function getParent()
    {
        return TextType::class;
    }

    public function getName()
    {
        return 'template';
    }
}