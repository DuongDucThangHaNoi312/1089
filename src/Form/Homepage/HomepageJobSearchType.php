<?php

namespace App\Form\Homepage;

use App\Entity\City\State;
use App\Entity\JobTitle\Lookup\JobTitleName;
use App\Repository\City\StateRepository;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

/**
 * Class HomepageJobSearchType
 * @package App\Form\Homepage
 */
class HomepageJobSearchType extends AbstractType
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * HomepageJobSearchType constructor.
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //$choicesJobTitleNames = $this->em->getRepository(JobTitleName::class)->findAllVisible();

//        if ($options['hasJobTitles']) {
//
//            $builder->add('jobTitleNames', Select2EntityType::class, [
//                'placeholder'          => 'Type desired job title (no abbreviations, please)',
//                'label'                => false,
//                'class'                => JobTitleName::class,
//                'allow_clear'          => true,
//                'remote_route'         => 'search_job_title_name',
//                'multiple'             => true,
//                'minimum_input_length' => 2,
//                'page_limit'           => getenv('PAGE_SIZE'),
//                'scroll'               => true,
//                'required' => false,
//                'attr'     => [
//                    'class' => 'js-jobTitles'
//                ],
//            ]);
//        }

        // CIT-782: make California the default selected value for state.
        $defaultState = $this->em->getRepository(State::class)->findOneByName('California');
        
        $builder
            ->add('state', EntityType::class, [
                'query_builder' => function (StateRepository $sr) {
                    return $sr->createQueryBuilder('s')
                              ->where('s.isActive = true')
                              ->orderBy('s.name');
                },
                'class'         => State::class,
                'attr'          => [
                    'class' => 'js-state'
                ],
                'required'      => false,
                'label'         => false,
                'placeholder'   => 'Select Active State',
                'data'          => $defaultState
            ])
            ->add('county', ChoiceType::class, [
                'choices'     => [],
                'attr'        => [
                    'class' => 'js-counties'
                ],
                'label'       => false,
                'placeholder' => 'Select County'
            ])
            ->add('search', SubmitType::class, [
                'attr' => [
                    'class' => 'btn-submit btn btn-homepage-job-search'
                ],
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
            'hasJobTitles' => true
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'homepage_job_search';
    }
}
