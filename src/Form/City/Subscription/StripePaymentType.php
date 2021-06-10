<?php

namespace App\Form\City\Subscription;

use App\Entity\City;
use App\Entity\SubscriptionPlan\CitySubscriptionPlan;
use App\Repository\SubscriptionPlan\CitySubscriptionPlanRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StripePaymentType extends AbstractType
{
    /** @var \Twig\Environment $twig */
    private $twig;

    private $entityManager;

    public function __construct(\Twig\Environment $twig, EntityManagerInterface $entityManager)
    {
        $this->twig = $twig;
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $city = $options['city'];
        $subscriptionPlanQuery = null;
        if ($city instanceof City) {
            $subscriptionPlanQuery = function(CitySubscriptionPlanRepository $subscriptionPlanRepository) {
                return $subscriptionPlanRepository->createQueryBuilder('subscription_plan')
                    ->andWhere('subscription_plan.isTrial = false')
                    ->andWhere('subscription_plan.isActive = true')
                    ->orderBy('subscription_plan.price');
            };
        }



        $builder
            ->add('subscriptionPlans', EntityType::class, [
                'label' => false,
                'class' => CitySubscriptionPlan::class,
                'expanded' => true,
                'multiple' => false,
                'required' => true,
                // CIT-780: If A City has a SubscriptionChangeRequest in queue do not allow them to change their subscription until they cancel a Subscription
                'disabled' => $city->getSubscription()->getSubscriptionChangeRequest() == null ? false : true,
                'query_builder' => $subscriptionPlanQuery,
                'choice_label' => function(CitySubscriptionPlan $subscriptionPlan) use (&$city) {
                    return $this->getSubscriptionPlanLabelTemplate($subscriptionPlan, $city);
                }
            ])
        ;
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $city = $options['city'];
        foreach($view->children['subscriptionPlans']->children as $subscriptionPlanChoice) {
            $subscriptionPlan = $this->entityManager->getRepository(CitySubscriptionPlan::class)->find(strval($subscriptionPlanChoice->vars["value"]));
            if ($subscriptionPlan) {
                if (!$subscriptionPlan->isCityCompliant($city)) {
                    $subscriptionPlanChoice->vars['attr']['disabled'] = 'disabled';
                }
            }
        }
    }

    /**
     * @param CitySubscriptionPlan $subscriptionPlan
     * @param City $city
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function getSubscriptionPlanLabelTemplate(CitySubscriptionPlan $subscriptionPlan, City $city) {
        return $this->twig->render('city/subscription/subscription_plan_choice_label.html.twig', [
            'plan' => $subscriptionPlan,
            'city' => $city,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('city');
    }
}