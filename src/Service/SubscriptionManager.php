<?php

namespace App\Service;

use App\Entity\City;
use App\Entity\JobAnnouncement;
use App\Entity\JobAnnouncement\Lookup\JobAnnouncementStatus;
use App\Entity\SubscriptionPlan;
use App\Entity\SubscriptionPlan\CitySubscriptionPlan;
use App\Entity\SubscriptionPlan\JobSeekerSubscriptionPlan;
use App\Entity\User\JobSeekerUser;
use App\Entity\User\Subscription;
use App\Entity\User\SubscriptionChangeRequest;
use DateTime;
use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SubscriptionManager
{
    private $em;

    private $subscriptionProcessor;

    /**
     * SubscriptionManager constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em, SubscriptionProcessorInterface $subscriptionProcessor)
    {
        $this->em = $em;
        $this->subscriptionProcessor = $subscriptionProcessor;
    }

    /**
     * @param City $city
     * @param CitySubscriptionPlan $citySubscriptionPlan
     * @throws Exception
     */
    public function subscribeCity(City $city, CitySubscriptionPlan $citySubscriptionPlan, bool $isPaid = false, $expiresOn = null)
    {
        $expirationString = 'now + 1 ' . $citySubscriptionPlan->getRenewalFrequency()->determineInterval();
        if ($citySubscriptionPlan->getIsTrial()) {
            $expirationString = 'now + 4 months';
        }

        if ($expiresOn == null) {
            $expiresOn = new DateTime($expirationString, new \DateTimeZone('UTC'));
        }
        try {
            if ($city->getSubscription()) {
                $city->getSubscription()
                    ->setSubscriptionPlan($citySubscriptionPlan)
                    ->setExpiresAt($expiresOn)
                    ->setWillCancelOn(null)
                    ->setCancellationRequestedAt(null)
                    ->setCancelledAt(null)
                    ->setIsPaid($isPaid)
                ;
            } else {
                $subscription = new City\Subscription();
                $subscription
                    ->setCity($city)
                    ->setSubscriptionPlan($citySubscriptionPlan)
                    ->setExpiresAt($expiresOn)
                    ->setWillCancelOn(null)
                    ->setCancellationRequestedAt(null)
                    ->setCancelledAt(null)
                    ->setIsPaid($isPaid)
                ;
                $city->setSubscription($subscription);
                $this->em->persist($subscription);
            }
            $this->em->persist($city);
            $this->em->flush();
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * CIT-780: For City Users, subscription changes should be done immediately, but hold off on Stripe charge.
     * Should ideally be executed after a queueSubscriptionChange method.
     * The method can also be used to revert the SubscriptionLocally upon cancel.
     * @param Subscription $subscription
     * @param SubscriptionPlan $subscriptionPlan
     */
    public function changeSubscriptionLocally(Subscription $subscription, SubscriptionPlan $subscriptionPlan) {
        $subscription->setSubscriptionPlan($subscriptionPlan);
        $this->em->persist($subscription);
        $this->em->flush();
    }

    public function queueSubscriptionChange(Subscription $subscription, SubscriptionPlan $subscriptionPlan) {
        $subscriptionChangeRequestRepository = $this->em->getRepository(SubscriptionChangeRequest::class);
        $subscriptionChangeRequest = $subscriptionChangeRequestRepository->findOneBy(['subscription' => $subscription->getId()]);
        if (!$subscriptionChangeRequest) {
            $subscriptionChangeRequest = new SubscriptionChangeRequest();
            $subscriptionChangeRequest
                ->setSubscription($subscription);
        }

        $subscriptionChangeRequest
            ->setNewSubscriptionPlan($subscriptionPlan)
            ->setOldSubscriptionPlan($subscription->getSubscriptionPlan())
            ->setChangeOn($subscription->getExpiresAt())
            ->setRequestedAt(new DateTime('now'));

        $this->em->persist($subscriptionChangeRequest);
        $this->em->flush();
    }

    /**
     * @param City $city
     * @param CitySubscriptionPlan $citySubscriptionPlan
     * @throws Exception
     */
    public function reactivateCitySubscription(City $city, CitySubscriptionPlan $citySubscriptionPlan, bool $isPaid = false, $expiresOn = null)
    {
        try {
            /** Only Reset the expiration date only if Subscription has been cancelled after it has been Cancelled. */
            if ($city->getSubscription()->getWillCancelOn() != null && new DateTime('now', new \DateTimeZone('UTC')) > $city->getSubscription()->getWillCancelOn()) {
                if ($expiresOn) {
                    $city->getSubscription()->setExpiresAt($expiresOn);
                } else {
                    $city->getSubscription()->setExpiresAt(new DateTime('+1 ' . $citySubscriptionPlan->getRenewalFrequency()->determineInterval(), new \DateTimeZone('UTC')));
                }
            }
            $city->getSubscription()
                ->setSubscriptionPlan($citySubscriptionPlan)
                ->setWillCancelOn(null)
                ->setCancellationRequestedAt(null)
                ->setCancelledAt(null)
                ->setIsPaid($isPaid)
            ;
            $this->em->persist($city);
            $this->em->flush();
        } catch (Exception $e) {
            throw $e;
        }

    }

    /**
     * @param City\Subscription $subscription
     * @throws Exception
     */
    public function cancelCitySubscription(City\Subscription $subscription)
    {
        try {
            $subscription->setCancelledAt(new DateTime('now', new \DateTimeZone('UTC')));
            /** @var JobAnnouncementStatus $status */
            $status = $this->em->getReference(JobAnnouncementStatus::class, JobAnnouncement::STATUS_ARCHIVED);
            $this->cancelSubscriptionRequest($subscription->getSubscriptionChangeRequest(), false, true);
            $subscription->getCity()->setAllJobAnnouncementsToStatus($status);
            $this->em->persist($subscription);
            $this->em->flush();
        } catch (Exception $e) {
            throw $e;
        }

    }

    public function renewSubscription(Subscription $subscription,DateTime $expirationDate,  bool $isPaid = false) {
        $subscription
            ->setExpiresAt($expirationDate)
            ->setIsPaid($isPaid);

        $this->em->persist($subscription);
        $this->em->flush();
    }

    /**
     * @param JobSeekerUser $jobSeekerUser
     * @param JobSeekerSubscriptionPlan $jobSeekerSubscriptionPlan
     * @throws Exception
     */
    public function subscribeJobSeeker(JobSeekerUser $jobSeekerUser, JobSeekerSubscriptionPlan $jobSeekerSubscriptionPlan, bool $isPaid = false, bool $isTrial = false, $expiresOn = null)
    {
        try {
            $jobSeekerSubscription = $jobSeekerUser->getSubscription() ? $jobSeekerUser->getSubscription() : new JobSeekerUser\Subscription();

            /** JobSeekerSubscriptions that have never been part of Stripe, should increment their expiration Date when they are subscribed */
            if ($jobSeekerSubscription->getPaymentProcessorSubscriptionId() == "") {
                $jobSeekerSubscription->setExpiresAt(new DateTime('+1 ' . $jobSeekerSubscriptionPlan->getRenewalFrequency()->determineInterval(), new \DateTimeZone('UTC')));
            }

            /** New JobSeekerSubscriptions or JobSeekerSubscriptions that have been reactivated after cancelled should have expiration date set/reset. */
            if ($jobSeekerSubscription->getId() == null || $jobSeekerUser->getSubscription()->getSubscriptionPlan()->getId() == JobSeekerSubscriptionPlan::JOB_SEEKER_TRIAL_PLAN_ID || ($jobSeekerSubscription->getWillCancelOn() != null && new DateTime('now', new \DateTimeZone('UTC')) > $jobSeekerSubscription->getWillCancelOn())) {
                if ($isTrial) {
                    $jobSeekerSubscription->setExpiresAt(new DateTime('+14 days', new \DateTimeZone('UTC')));
                } else {
                    if ($expiresOn) {
                        $jobSeekerSubscription->setExpiresAt($expiresOn);
                    } else {
                        $jobSeekerSubscription->setExpiresAt(new DateTime('+1 ' . $jobSeekerSubscriptionPlan->getRenewalFrequency()->determineInterval(), new \DateTimeZone('UTC')));
                    }
                }
            }

            $jobSeekerSubscription->setSubscriptionPlan($jobSeekerSubscriptionPlan)
                ->setJobSeekerUser($jobSeekerUser)
                ->setWillCancelOn(null)
                ->setCancellationRequestedAt(null)
                ->setCancelledAt(null)
                ->setIsPaid($isPaid)
            ;

            $jobSeekerUser->setSubscription($jobSeekerSubscription);

            $this->em->persist($jobSeekerSubscription);
            $this->em->persist($jobSeekerUser);
            $this->em->flush();
        } catch (Exception $e) {
            throw $e;
        }

    }

    public function setFutureCancellation(Subscription $subscription, $isCity = false)
    {
        $subscription
            ->setCancellationRequestedAt(new DateTime('now', new \DateTimeZone('UTC')))
            ->setWillCancelOn($subscription->getExpiresAt())
        ;
        $this->em->persist($subscription);
        $this->em->flush();
        $this->cancelSubscriptionRequest($subscription->getSubscriptionChangeRequest(), $isCity);
    }

    /**
     * @param JobSeekerUser\Subscription $subscription
     * @throws Exception
     */
    public function cancelJobSeekerSubscription(JobSeekerUser\Subscription $subscription)
    {
        try {
            $subscription->setCancelledAt(new DateTime('now', new \DateTimeZone('UTC')));
            $subscription->getJobSeekerUser()->removeAllSubmittedJobTitleInterest();
            $this->em->persist($subscription);
            $this->em->flush();
            $this->cancelSubscriptionRequest($subscription->getSubscriptionChangeRequest());
        } catch (Exception $e) {
            throw $e;
        }

    }


    public function cancelSubscriptionRequest(SubscriptionChangeRequest $subscriptionChangeRequest = null, $directDelete = false, $isCity = false) {
        if ($subscriptionChangeRequest) {

            if ($directDelete) {
                try {
                    if ($isCity) {
                        // CIT-780: When we are cancelling a Subscription Request, set the Subscription back to it's original state, matching the Stripe Subscription.
                        $this->changeSubscriptionLocally($subscriptionChangeRequest->getSubscription(), $subscriptionChangeRequest->getOldSubscriptionPlan());
                    }
                    $this->em->getConnection()
                        ->delete('subscription_change_request', ['id' => $subscriptionChangeRequest->getId()]);
                    return true;
                } catch(Exception $e) {
                    return false;
                }
            }

            $fetchedSubscriptionRequest = $this->em->getRepository(SubscriptionChangeRequest::class)->find($subscriptionChangeRequest->getId());
            try {
                if ($isCity) {
                    // CIT-780: When we are cancelling a Subscription Request, set the Subscription back to it's original state, matching the Stripe Subscription.
                    $this->changeSubscriptionLocally($subscriptionChangeRequest->getSubscription(), $subscriptionChangeRequest->getOldSubscriptionPlan());
                }
                $this->em->remove($fetchedSubscriptionRequest);
                $this->em->flush();
                return true;
            } catch(Exception $e) {
                return false;
            }

        }

        return false;
    }

    public function setIsPaid(Subscription $subscription) {
        $subscription
            ->setIsPaid(true);
        $this->em->persist($subscription);
        $this->em->flush();
    }

    public function isUpgrade(Subscription $subscription, SubscriptionPlan $subscriptionPlan) {
        if ($subscription instanceof City\Subscription && $subscriptionPlan instanceof CitySubscriptionPlan) {
            $city = $subscription->getCity();
            return $subscription->getSubscriptionPlan()->getPriceByFTE($city->getCountFTE()) <= $subscriptionPlan->getPriceByFTE($city->getCountFTE());
        }
        return $subscription->getSubscriptionPlan()->getPrice() <= $subscriptionPlan->getPrice();
    }

    public function processCitySubscription(City $city, CitySubscriptionPlan $citySubscriptionPlan, FlashBagInterface $flashBag, $token = null) {
        // Only activate the Stripe Subscription process for subscription plans that there plan is more than 0.0 dollars.
        $isSubscribed = false;
        $isPaid = false;
        $expiresOn = null;
        if (($citySubscriptionPlan->getPriceByFTE($city->getCountFTE()) > 0.0)||($citySubscriptionPlan->getPriceByFTE($city->getCountFTE()) == 0.0 && $city->getSubscriptionId() != '')) {
            $this->subscriptionProcessor->setFlashBag($flashBag);
            $user = $city->getAdminCityUser();
            if ($user) {
                $subscription = $this->subscriptionProcessor->processSubscription('city-membership', $citySubscriptionPlan, $user, $token);
                if ($subscription) {
                    $isSubscribed = $subscription != null ? true : false;
                    $isPaid = $subscription->status == 'active' || $subscription->status == 'canceled' || $subscription->status == 'trialing' ? true : false;
                    $timestamp = $subscription->current_period_end;
                    $expiresOn = new \DateTime('@'. $timestamp);
                    $user->setRawStripeCustomer($subscription->customer);
                    $city->getSubscription()->setRawStripeSubscription($subscription->id);
                    $city->getSubscription()->setExpiresAt($expiresOn);
                    $this->em->persist($city->getSubscription());
                    $this->em->persist($user);
                    $this->em->flush();
                    $this->cancelSubscriptionRequest($city->getSubscription()->getSubscriptionChangeRequest(), true, true);
                }
            }
        } else {
            $isSubscribed = true;
            $isPaid = true;
        }

        return ['isSubscribed' => $isSubscribed, 'isPaid' => $isPaid, 'expiresOn' => $expiresOn];

    }

    public function processJobSeekerSubscription(JobSeekerUser $user, JobSeekerSubscriptionPlan $jobSeekerSubscriptionPlan, FlashBagInterface $flashBag, $token = null) {

        $isSubscribed = false;
        $isPaid = false;
        $expiresOn = null;
        // Only activate the Stripe Subscription process for subscription plans that there plan is more than 0.0 dollars.
        if (($jobSeekerSubscriptionPlan->getPrice() > 0.0) || ($jobSeekerSubscriptionPlan->getPrice() == 0.0 && $user->getSubscriptionId() != '')) {
            $this->subscriptionProcessor->setFlashBag($flashBag);
            $subscription = $this->subscriptionProcessor->processSubscription('job-seeker-membership', $jobSeekerSubscriptionPlan, $user, $token);
            if ($subscription) {
                $isSubscribed = $subscription != null ? true : false;
                $timestamp = $subscription->current_period_end;
                $expiresOn = new \DateTime('@'. $timestamp);
                $isPaid = $subscription->status == 'active' || $subscription->status == 'cancelled' ? true : false;
                $user->setRawStripeCustomer($subscription->customer);
                $user->getSubscription()->setRawStripeSubscription($subscription->id);
                $user->getSubscription()->setExpiresAt($expiresOn);
                $this->em->persist($user->getSubscription());
                $this->em->persist($user);
                $this->removeSubmittedJobTitleInterestWithUnsuitableLevel($jobSeekerSubscriptionPlan, $user);
                $this->em->flush();
                $this->cancelSubscriptionRequest($user->getSubscription()->getSubscriptionChangeRequest(), true);
            }
        } else {
            $isSubscribed = true;
            $isPaid = true;
        }

        return ['isSubscribed' => $isSubscribed, 'isPaid' => $isPaid, 'expiresOn' => $expiresOn];
    }

    /**
     * CIT-626: When a Job Seeker changes subscription level... all Submitted Interest in Job Titles outside the Levels of the current subscription plan, should be deleted.
     *
     * @param JobSeekerSubscriptionPlan $newSubscriptionPlan
     * @param JobSeekerUser $user
     */
    public function removeSubmittedJobTitleInterestWithUnsuitableLevel(JobSeekerSubscriptionPlan $newSubscriptionPlan, JobSeekerUser $user)
    {
        $allowedJobLevelIds = [];
        foreach ($newSubscriptionPlan->getAllowedJobLevels() as $level) {
            $allowedJobLevelIds[] = $level->getId();
        }

        foreach ($user->getSubmittedJobTitleInterests() as $jti) {
            if ( ! in_array($jti->getJobTitle()->getLevel()->getId(), $allowedJobLevelIds)) {
                $user->removeSubmittedJobTitleInterest($jti);
            }
        }

        $this->em->flush();
    }
}