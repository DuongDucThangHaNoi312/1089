<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\AlertedJobAnnouncement;
use App\Repository\AlertedJobAnnouncementRepository;
use App\Repository\User\JobSeekerUserRepository;
use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\ORM\EntityRepository;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Filter\ModelAutocompleteFilter;
use Sonata\Form\Type\DatePickerType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

final class AlertedJobAnnouncementAdmin extends AbstractAdmin
{
    public function setPagerType($pagerType)
    {
        parent::setPagerType('simple'); // TODO: This is a workaround because I can't set 'pager_type: "simple"' to work in services.yaml
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $value = new \DateTime();
        $datagridMapper
            ->add('createdFrom', 'doctrine_orm_callback', array(
                'label'       => 'Sent From',
                'show_filter' => true,
                'callback'    => function ($queryBuilder, $alias, $field, $value) {
                    if ( ! $value) {
                        return;
                    }

                    $time     = new \DateTime($value['value']->format('Y-m-d 00:00:00'), new \DateTimeZone('America/Los_Angeles'));
                    $timeZone = (new \Datetime())->getTimezone();

                    $queryBuilder->andWhere($alias . '.createdAt >= :createdFrom');
                    $queryBuilder->setParameter('createdFrom', ($time->setTimezone($timeZone))->format('Y-m-d H:i:m'));

                    return true;
                },
                'field_type'  => DatePickerType::class
            ))
            ->add('createdTo', 'doctrine_orm_callback', array(
                'label'       => 'Sent To',
                'show_filter' => true,
                'callback'    => function ($queryBuilder, $alias, $field, $value) {
                    if ( ! $value) {
                        return;
                    }

                    $time     = new \DateTime($value['value']->format('Y-m-d 23:59:59'), new \DateTimeZone('America/Los_Angeles'));
                    $timeZone = (new \Datetime())->getTimezone();

                    $queryBuilder->andWhere($alias . '.createdAt <= :createdTo');
                    $queryBuilder->setParameter('createdTo', ($time->setTimezone($timeZone))->format('Y-m-d H:i:m'));

                    return true;
                },
                'field_type'  => DatePickerType::class
            ))
            ->add('jobAnnouncement')
            ->add('jobAnnouncement.status', null, [
                'label'=> 'Status'
            ])
            ->add('jobAnnouncement.jobTitle', ModelAutocompleteFilter::class, [
                'label' => 'Job Title',
            ],
                null,
                [
                    'callback' => function ($admin, $property, $value) {
                        $qb = $admin->getDatagrid()->getQuery();
                        $alias = $qb->getRootAlias();
                        $qb->join($alias . '.jobTitle',  'jt')
                           ->join('jt.jobTitleName',  'jtn')
                           ->where("jtn.name LIKE :search")
                           ->setParameter('search', '%'.$value.'%');
                    },
                    'property'           => 'name'
                ]
            )
            ->add('jobAnnouncement.jobTitle.city', ModelAutocompleteFilter::class, [
                'label' => 'City'
            ],
                null,
                [
                    'callback'           => function ($admin, $property, $value) {
                        $qb = $admin->getDatagrid()->getQuery();
                        $qb
                            ->join($qb->getRootAlias() . '.counties', 'county')
                            ->join('county.state', 'state')
                            ->orderBy('state.name', 'ASC')
                            ->where($qb->getRootAlias() . ".name LIKE :search")
                            ->setParameter('search', '%' . $value . '%');
                    },
                    'attr'               => [
                        'class' => 'ja-filter-city'
                    ],
                    'property'           => 'name',
                    'to_string_callback' => function ($entity) {
                        return $entity->getCityAndState();
                    }
                ]
            )
            ->add('jobSeeker');
    }

    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);

        $request    = $this->getRequest();
        $sortFilter = $request->query->get('filter');

        if (isset($sortFilter['jobAnnouncement__jobTitle__city']) && $sortFilter['jobAnnouncement__jobTitle__city']['value'] !== ''
            && isset($sortFilter['jobAnnouncement__jobTitle']) && $sortFilter['jobAnnouncement__jobTitle']['value'] !== '') {
            $query->join($query->getRootAliases()[0] . '.jobAnnouncement', 'ja')
                  ->join('ja.jobTitle', 'jt')
                  ->join('jt.city', 'ct')
                  ->where('ct.id = :cityId')
                  ->andWhere('jt.id = :jobTitleId')
                  ->setParameter('cityId', $sortFilter['jobAnnouncement__jobTitle__city']['value'])
                  ->setParameter('jobTitleId', $sortFilter['jobAnnouncement__jobTitle']['value'])
                  ->setMaxResults(1);
        } else {
            $query->join($query->getRootAliases()[0] . '.jobAnnouncement', 'ja')
                  ->join('ja.status', 'jas')
                  ->join('ja.jobTitle', 'jt')
                  ->join('jt.city', 'ct')
                  ->leftJoin('jt.jobTitleName', 'jn')
                  ->groupBy('jas.id')
                  ->addGroupBy('jt.id')
                  ->orderBy('jn.name', 'asc')
                  ->setMaxResults(1);
        }

        return $query;
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $request    = $this->getRequest();
        $sortFilter = $request->query->get('filter');

        $listMapper
            ->add('jobAnnouncement.jobTitle.city.stateFromCounty', 'array', [
                'label'    => 'State',
                'template' => 'admin/list/custom_state_from_city_list.html.twig'
            ])
            ->add('jobAnnouncement.jobTitle.city.counties', null, [
                'label' => 'County'
            ])
            ->add('jobAnnouncement.jobTitle.city', null, [
                'label' => 'City'
            ])
            ->add('jobAnnouncement.jobTitle', null, [
                'label'    => 'Job Title',
                'template' => 'admin/list/alert_link_to_job_announcement.html.twig'
            ])
            ->add('jobAnnouncement.status');

        if (isset($sortFilter['jobAnnouncement__jobTitle__city']) && $sortFilter['jobAnnouncement__jobTitle__city']['value'] !== '') {
            $listMapper
                ->add('jobSeeker')
                ->add('createdAt', null, [
                    'label' => 'Sent At'
               ]);
        } else {
            $listMapper->add('alertedSend', 'array', [
                'label'    => 'Sent Alert(s)',
                'template' => 'admin/list/custom_alerted_job_announcement_list.html.twig',

            ]);
            $listMapper->add('createdAt', null, [
                'label'    => 'Date',
                'template' => 'admin/list/alert_format_date.html.twig',
                'format'   => 'm/d/Y'
            ]);
        }

        $listMapper->add('_action', null, [
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
            ->add('jobAnnouncement')
            ->add('jobSeeker');
    }

    protected function configureShowFields(ShowMapper $showMapper): void
    {
        $showMapper
            ->add('id')
            ->add('jobAnnouncement')
            ->add('jobSeeker')
            ->add('createdAt')
            ->add('updatedAt');
    }
}
