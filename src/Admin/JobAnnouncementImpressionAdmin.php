<?php

declare(strict_types=1);

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\Form\Type\DatePickerType;

final class JobAnnouncementImpressionAdmin extends AbstractAdmin
{

    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('id')
            ->add('jobAnnouncement')
            ->add('jobSeekerUser')
            ->add('createdFrom', 'doctrine_orm_callback', array(
                'label'          => 'Viewed at From',
                'show_filter'    => true,
                'field_type'     => DatePickerType::class,
                'model_timezone' => 'UTC',
                'view_timezone'  => 'America/Los_Angeles',
                'callback'       => function ($queryBuilder, $alias, $field, $value) {
                    if ( ! $value) {
                        return;
                    }

                    $time     = new \DateTime($value['value']->format('Y-m-d 00:00:00'), new \DateTimeZone('America/Los_Angeles'));
                    $timeZone = (new \Datetime())->getTimezone();

                    $queryBuilder->andWhere($alias . '.createdAt >= :createdFrom');
                    $queryBuilder->setParameter('createdFrom', ($time->setTimezone($timeZone))->format('Y-m-d H:i:m'));

                    return true;
                },
            ))
            ->add('createdTo', 'doctrine_orm_callback', array(
                'label'          => 'Viewed at To',
                'show_filter'    => true,
                'field_type'     => DatePickerType::class,
                'model_timezone' => 'UTC',
                'view_timezone'  => 'America/Los_Angeles',
                'callback'       => function ($queryBuilder, $alias, $field, $value) {
                    if ( ! $value) {
                        return;
                    }
                    $time     = new \DateTime($value['value']->format('Y-m-d 23:59:59'), new \DateTimeZone('America/Los_Angeles'));
                    $timeZone = (new \Datetime())->getTimezone();

                    $queryBuilder->andWhere($alias . '.createdAt <= :createdTo');
                    $queryBuilder->setParameter('createdTo', ($time->setTimezone($timeZone))->format('Y-m-d H:i:m'));

                    return true;
                },
            ))
            ->add('userAgent');
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->add('id')
            ->add('jobAnnouncement')
            ->add('jobSeekerUser')
            ->add('ipAddress')
            ->add('userAgent')
            ->add('createdAt')
            ->add('_action', null, [
                'actions' => [
                    'show'   => [],
                    'edit'   => [],
                    'delete' => [],
                ],
            ]);
    }

    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
            ->add('id')
            ->add('ipAddress')
            ->add('userAgent')
            ->add('createdAt')
            ->add('updatedAt');
    }

    protected function configureShowFields(ShowMapper $showMapper): void
    {
        $showMapper
            ->add('id')
            ->add('ipAddress')
            ->add('userAgent')
            ->add('createdAt')
            ->add('updatedAt');
    }
}
