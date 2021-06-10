<?php

namespace App\Service;

use App\Entity\City;
use App\Entity\SubscriptionPlan;
use App\Entity\User;

interface SubscriptionProcessorInterface  {

    public function processSubscription(string $productId, SubscriptionPlan $subscriptionPlan, User $user, ?string $token);

    public function updateSubscription(User $user, $subscription = null, $plan = null);

    public function updateCustomerPaymentMethod(User $user, ?string $token);

    public function cancelSubscription(string $id);

    public function reactivateSubscription(User $user, string $id);

    public function deactivatePlan(string $planId);

    public function activatePlan(string $planId);

    public function getProduct(string $id);

    public function updateSubscriptionAfterPlanChange($subscription = null, $plan = null);

    public function updateCityPlan(SubscriptionPlan $subscriptionPlan, City $city);

    public function updatePlan(SubscriptionPlan $subscriptionPlan);

    public function retrieveSubscription(string $subscriptionId);

    public function getPlan(SubscriptionPlan $subscriptionPlan, $product = null);

    public function getCustomer(User $user, ?string $token);

    public function getSubscription(User $user, $customer = null, $plan = null);

    public function retrieveInvoices(string $customerId, int $limit = 24, ?string $afterInvoiceId = null);

    public function retrieveCustomerPaymentMethod(string $customerId);

    public function paySubscription(string $subscriptionId);

    public function previewUpcomingInvoice(User $user, $subscription, SubscriptionPlan $subscriptionPlan, string $productId);
 }