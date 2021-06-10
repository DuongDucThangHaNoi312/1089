<?php

namespace App\Admin;

use App\Form\Type\TemplateType;
use App\Repository\City\StateRepository;
use App\Repository\Lookup\UrlTypeRepository;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Filter\ModelAutocompleteFilter;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class UrlAdmin extends AbstractAdmin
{
    protected function configureRoutes(RouteCollection $collection) {
        parent::configureRoutes($collection);

        $collection->add('testUrl', $this->getRouterIdParameter() . '/test_url');
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {

        $datagridValues = $this->getDatagrid()->getValues();

        $datagridMapper
            ->add('city.counties.state', null, [
                'label' => 'State',
                'show_filter' => true
            ], null, [
                'query_builder' => function (StateRepository $r) {
                    return $r->createQueryBuilder('s')
                        ->orderBy('s.name')
                    ;
                }
            ])
            ->add('city.counties', ModelAutocompleteFilter::class, [
                'label' => 'County',
                'show_filter' => true,
            ], null, [
                'property' => 'name',
                'to_string_callback' => function($entity) {
                    return $entity->getDisplayName();
                },
            ])
            ->add('city', ModelAutocompleteFilter::class, [
                'show_filter' => true
            ], null, [
                'callback' => function ($admin, $property, $value) {

                    $qb = $admin->getDatagrid()->getQuery();

                    $qb->join($qb->getRootAlias() . '.counties', 'county')
                        ->join('county.state', 'state')
                        ->addOrderBy($qb->getRootAlias() . '.name', 'ASC')
                        ->addOrderBy('state.name', 'ASC')
                        ->where($qb->getRootAlias() . ".name LIKE :search")
                        ->setParameter('search', '%' . $value . '%');
                },
                'attr' => [
                    'class' => 'ja-filter-city'
                ],
                'property' => 'name',
                'to_string_callback' => function ($entity) {
                    return $entity->getCityAndState();
                }
//                'query_builder' => function (CityRepository $r) use (&$datagridValues) {
//                    if (isset($datagridValues['city__counties']) && $datagridValues['city__counties']['value']) {
//                        return $r->createQueryBuilder('c')
//                            ->join('c.counties', 'counties')
//                            ->where('counties = :county')
//                            ->orderBy('c.name')
//                            ->setParameter('county', $datagridValues['city__counties']['value'])
//                            ;
//                    } elseif (isset($datagridValues['city__counties__state']) && $datagridValues['city__counties__state']['value']) {
//                        return $r->createQueryBuilder('c')
//                            ->join('c.counties', 'counties')
//                            ->join('counties.state', 'state')
//                            ->where('state = :state')
//                            ->orderBy('c.name')
//                            ->setParameter('state', $datagridValues['city__counties__state']['value'])
//                            ;
//                    }
//                    return $r->createQueryBuilder('c')
//                        ->orderBy('c.name')
//                    ;
//                }
            ]);
//            if (isset($datagridValues['city']) && $datagridValues['city']['value']) {
//                $datagridMapper
//                    ->add('city.departments', null, [
//                        'label' => 'Departments',
//                        'show_filter' => true
//                    ], null, [
//                        'query_builder' => function (DepartmentRepository $r) use (&$datagridValues) {
//                            if (isset($datagridValues['city']) && $datagridValues['city']['value']) {
//                                return $r->createQueryBuilder('d')
//                                    ->where('d.city = :city')
//                                    ->orderBy('d.name')
//                                    ->setParameter('city', $datagridValues['city']['value']);
//                            }
//                            return $r->createQueryBuilder('d')
//                                ->orderBy('d.name');
//                        }
//                    ]);
//            }
//            if (isset($datagridValues['city']) && $datagridValues['city']['value']) {
//                $datagridMapper->add('city.jobTitles', null, [
//                    'label' => 'Job Titles',
//                    'show_filter' => true,
//                ], null, [
//                    'query_builder' => function (JobTitleRepository $r) use (&$datagridValues) {
//                        if (isset($datagridValues['city__departments']) && $datagridValues['city__departments']['value']) {
//                            return $r->createQueryBuilder('jt')
//                                ->where('jt.department = :dept')
//                                ->join('jt.jobTitleName', 'jtn')
//                                ->orderBy('jtn.name')
//                                ->setParameter('dept', $datagridValues['city__departments']['value'])
//                                ;
//                        } elseif (isset($datagridValues['city']) && $datagridValues['city']['value']) {
//                            return $r->createQueryBuilder('jt')
//                                ->where('jt.city = :city')
//                                ->join('jt.jobTitleName', 'jtn')
//                                ->orderBy('jtn.name')
//                                ->setParameter('city', $datagridValues['city']['value'])
//                                ;
//                        }
//                        return $r->createQueryBuilder('jt')
//                            ->join('jt.jobTitleName', 'jtn')
//                            ->orderBy('jtn.name')
//                            ;
//                    }
//                ]);
//            }
            $datagridMapper->add('type', null, [
                'show_filter' => true,
                'query_builder' => function (UrlTypeRepository $r) {
                    return $r->createQueryBuilder('ut')
                        ->orderBy('ut.name')
                        ;
                }

            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('city')
            ->add('city.counties', null, [
                'label' => 'County'
            ])
            ->add('city.counties.state', null, [
                'label' => 'State'
            ])
            ->add('type')
            ->add('value')
            ->add('clickCount')
            ->add('testUrl', null, [
                'template' => 'admin/list_test_url_action.html.twig'
            ])
            ->add('lastTestedDate')
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
        if ('app.admin.city' != $this->getRootCode()) {
            $formMapper
                ->add('city', ModelAutocompleteType::class, [
                    'property' => 'name',
                    'to_string_callback' => function($entity) {
                        return $entity->getCityAndState();
                    },
                ], ['custom_field_size' => 9]);
        }
        $formMapper
            ->add('type', ModelType::class, [
                'placeholder' => 'Add or Select a Type'
            ], ['custom_field_size' => 4])
            ->add('value', null ,[], ['custom_field_size' => 4])
            ->add('testUrl', TemplateType::class, [
                'mapped' => false,
                'template' => 'admin/edit_test_url_action.html.twig'
            ], ['custom_field_size' => 2])
            ->add('lastTestedDate', DateType::class, [
                'placeholder' => 'Not Tested Yet',
                'disabled' => true,
                'widget' => 'single_text',
                'format' => 'M/d/Y',
            ], ['custom_field_size' => 2])
        ;
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('type')
            ->add('value')
            ->add('city')
        ;
    }
}
