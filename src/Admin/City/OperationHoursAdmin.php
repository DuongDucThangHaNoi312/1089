<?php

namespace App\Admin\City;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;

class OperationHoursAdmin extends AbstractAdmin
{
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('day')
            ->add('open')
            ->add('close')
            ->add('city')
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
            ->add('day')
            ->add('open')
            ->add('close')
            ->add('city')
            ->add('_action', null, [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $request = $this->getRequest()->getRequestUri();

        if (false !== strpos($request, 'operation') and (false == strpos($request, 'city'))) {
            $formMapper
//            ->add('id')
                ->add('city', null, [], ['custom_field_size' => 9]);
        }
        $formMapper
            ->add('day', ChoiceType::class, [
                'choices' => [
                    'Monday' => 'monday',
                    'Tuesday' => 'tuesday',
                    'Wednesday' => 'wednesday',
                    'Thursday' => 'thursday',
                    'Friday' => 'friday',
                    'Saturday' => 'saturday',
                    'Sunday' => 'sunday',
                ]
            ], ['custom_field_size' => 4])
            ->add('open', TimeType::class, [
                'help' => 'Opening Time Format ->  Hour:Min AM/PM ',
                'widget' => 'single_text',
            ], ['custom_field_size' => 4])
            ->add('close', TimeType::class, [
                'help' => 'Closing Time Format ->  Hour:Min AM/PM ',
                'widget' => 'single_text',
            ], ['custom_field_size' => 4])
//            ->add('city')
        ;
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('day')
            ->add('open')
            ->add('close')
            ->add('city')
        ;
    }
}
