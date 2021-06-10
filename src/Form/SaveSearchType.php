<?php

namespace App\Form;

use App\Entity\User\JobSeekerUser\SavedSearch;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SaveSearchType extends AbstractType
{

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined('request');
        $resolver->setDefined('type');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        /** @var Request $request */
        $request = isset($options['request']) ? $options['request'] : null;
        $type = isset($options['type']) ? $options['type'] : null;

        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'attr' => [
                    'class' => 'save-search-name'
                ]
            ])
            ->add('queryString', HiddenType::class, [
                'data' => $request ? $request->getRequestUri() : null
            ])
            ->add('type', HiddenType::class, [
                'data' => $type ? $type : null
            ])
            ->add('Save', SubmitType::class, [
                'attr' => [
                    'class' => 'save-search-now-btn btn-primary btn'
                ]
            ])
            ->add('Cancel', ButtonType::class, [
                'attr' => [
                    'class' => 'btn btn-danger btn-close-save-search'
                ]
            ]);
        ;
    }

}
