<?php

namespace App\Form\JobSeeker\Subscription;

use App\Entity\SubscriptionPlan\JobSeekerSubscriptionPlan;
use App\Entity\User\CityUser;
use App\Entity\User\JobSeekerUser;
use App\Repository\SubscriptionPlan\CitySubscriptionPlanRepository;
use App\Repository\SubscriptionPlan\JobSeekerSubscriptionPlanRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StripePaymentType extends AbstractType
{
    /** @var \Twig\Environment $twig */
    private $twig;

    public function __construct(\Twig\Environment $twig)
    {
        $this->twig = $twig;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $options['user'];
        $subscriptionPlanQuery = null;
        if ($user instanceof JobSeekerUser) {
            $subscriptionPlanQuery = function(JobSeekerSubscriptionPlanRepository $subscriptionPlanRepository) {
                return $subscriptionPlanRepository->createQueryBuilder('subscription_plan')
                    ->andWhere('subscription_plan.isTrial = false')
                    ->andWhere('subscription_plan.isActive = true')
                    ->orderBy('subscription_plan.price');
            };
        }

        $builder
            ->add('subscriptionPlans', EntityType::class, [
                'label' => false,
                'class' => JobSeekerSubscriptionPlan::class,
                'expanded' => true,
                'multiple' => false,
                'required' => true,
                'query_builder' => $subscriptionPlanQuery,
                'choice_label' => function(JobSeekerSubscriptionPlan $subscriptionPlan) use (&$user) {
                    return $this->getSubscriptionPlanLabelTemplate($subscriptionPlan, $user);
                }
            ])
        ;
    }

    /**
     * @param JobSeekerSubscriptionPlan $subscriptionPlan
     * @param JobSeekerUser $user
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function getSubscriptionPlanLabelTemplate(JobSeekerSubscriptionPlan $subscriptionPlan, JobSeekerUser $user) {
        return $this->twig->render('job_seeker/subscription/subscription_plan_choice_label.html.twig', [
            'plan' => $subscriptionPlan,
            'user' => $user,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('user');
    }
}