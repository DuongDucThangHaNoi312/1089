<?php

namespace App\Admin\User;

use App\Admin\UserAdmin as BaseUserAdmin;
use App\Entity\Configuration;
use App\Entity\User\JobSeekerUser;
use App\EventListener\LoginListener;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Filter\ModelAutocompleteFilter;

class JobSeekerUserAdmin extends BaseUserAdmin
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var LoginListener
     */
    private $loginListener;


    public function __construct(
        $code,
        $class,
        $baseControllerName,
        EntityManagerInterface $entityManager,
        LoginListener $loginListener
    ) {
        parent::__construct($code, $class, $baseControllerName);

        $this->entityManager = $entityManager;
        $this->loginListener = $loginListener;
    }

    protected function configureDatagridFilters(DatagridMapper $filterMapper): void
    {
        parent::configureDatagridFilters($filterMapper);
        $filterMapper
            ->add('firstname')
            ->add('lastname')
            ->add('submittedJobTitleInterests.jobTitle', ModelAutocompleteFilter::class, [
                'label' => 'Submitted Job Title',
            ], null, [
                'property' => 'name',
                'callback' => function ($admin, $property, $value) {
                    $qb = $admin->getDatagrid()->getQuery();
                    $alias = $qb->getRootAlias();

                    $qb->join($alias . '.jobTitleName',  'jtn')
                        ->addOrderBy('jtn.name', 'ASC')
                        ->where("jtn.name LIKE :search")
                        ->setParameter('search', '%' . $value . '%');
                },
                'to_string_callback' => function($entity) {
                    return $entity->getName();
                },
            ])
            ->add('submittedJobTitleInterests.jobTitle.city', ModelAutocompleteFilter::class, [
                    'label' => 'Submitted City Name'
                ],
                null,
                [
                    'callback' => function ($admin, $property, $value) {
                        $qb = $admin->getDatagrid()->getQuery();

                        $qb
                            ->join($qb->getRootAlias() . '.counties', 'county')
                            ->join('county.state', 'state')
                            ->orderBy('state.name', 'ASC')
                            ->where($qb->getRootAlias() . ".name LIKE :search")
                            ->setParameter('search', '%'.$value.'%');
                        ;
                    },
                    'attr' => [
                        'class' => 'ja-filter-city'
                    ],
                    'property'           => 'name',
                    'to_string_callback' => function ($entity) {
                        return $entity->getCityAndState();
                    }
                ]
            )
        ;
    }

    public function createQuery($context = 'list')
    {
        $query      = parent::createQuery($context);
        $jobTitleId = $this->getRequest()->query->get('jobTitleId');

        if ('list' === $context) {
            $rootAlias = $query->getRootAliases()[0];
            if ($jobTitleId) {
                $query->join($rootAlias . '.submittedJobTitleInterests', 'smjt')
                      ->join('smjt.jobTitle', 'jobTitle')
                      ->where('jobTitle.id = :jobTitleId')
                      ->setParameter('jobTitleId', $jobTitleId);
            }

        }
        return $query;
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->addIdentifier('username')
            ->add('email')
            ->add('lastLogin')
            ->add('loginFrequency', null, [
                'template' => 'admin/list/job_seeker_login_frequency.html.twig'
            ])
            ->add('enabled', null, ['editable' => true])
            ->add('createdAt');

        if ($this->isGranted('ROLE_ALLOWED_TO_SWITCH')) {
            $listMapper
                ->add('impersonating', 'string', [
                    'template' => '@SonataUser/Admin/Field/impersonating.html.twig'
                ]);
        }
    }

    protected function configureFormFields(FormMapper $formMapper): void
    {
        parent::configureFormFields($formMapper);
    }

    protected function configureShowFields(ShowMapper $showMapper): void
    {
        parent::configureShowFields($showMapper);
    }

}
