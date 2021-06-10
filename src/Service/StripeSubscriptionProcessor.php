<?php

namespace App\Service;

use App\Entity\City;
use App\Entity\SubscriptionPlan;
use App\Entity\User;
use Miracode\StripeBundle\Manager\ModelManagerInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Session;

class StripeSubscriptionProcessor implements SubscriptionProcessorInterface {

    /** @var ModelManagerInterface $modelManager */
    private $modelManager;

    /** @var ?FlashBag $flashBag */
    private $flashBag;

    public function __construct(ModelManagerInterface $modelManager, ?FlashBag $flashBag)
    {
        $this->modelManager = $modelManager;
        $this->setFlashBag($flashBag);
    }

    public function setFlashBag(?FlashBag $flashBag) {
        $this->flashBag = $flashBag;
    }

    public function getFlashBag() {
        if (!$this->flashBag) {
            throw new \Exception('The FlashBag needs to be set in the ' . __CLASS__);
        }
        return $this->flashBag;
    }

    public function processSubscription(string $productId, SubscriptionPlan $subscriptionPlan, User $user, ?string $token) {
        $flashBag = $this->getFlashBag();
        $customer = $this->getCustomer($user, $token);
        $product = $this->getProduct($productId);
        if ($user instanceof User\CityUser) {
            $plan = $this->getCityPlan($subscriptionPlan, $product, $user->getCity());
        } else {
            $plan = $this->getPlan($subscriptionPlan, $product);
        }

        if ($customer) {
            $subscription = $this->getSubscription($user, $customer, $plan);
//            if ($subscription) {
//                return $this->modelManager->convert($subscription);
//            }
            return $subscription;
        }
        return null;
    }

    public function retrieveLatestInvoiceForSubscription(string $subscriptionId) {
        $subscription = $this->retrieveSubscription($subscriptionId);
        if ($subscription) {
            return $subscription->latest_invoice;
        }
        return null;
    }

    public function isUpgrade(\Stripe\Subscription $subscription, \Stripe\Plan $plan) {
        return $subscription->plan->amount <= $plan->amount;
    }

    public function updateSubscription(User $user, $subscription = null, $plan = null)
    {
        $flashBag = $this->getFlashBag();
        if ($plan instanceof \Stripe\Plan && $subscription instanceof \Stripe\Subscription) {
            $params = [
                'cancel_at_period_end' => false,
                'items' => [
                    [
                        'id' => $subscription->items->data[0]->id,
                        'plan' => $plan->id,
                    ] ,
                ],
            ];

            if ($subscription) {
                // If upgrade, credit and prorate
                if ($this->isUpgrade($subscription, $plan)) {
                    $params['billing_cycle_anchor'] = 'now';
                    $params['prorate'] = true;
                } else {
//                    $params['prorate'] = false;
                    $params['proration_date'] = $subscription->current_period_end;
                }

                try {
                    $subscription = \Stripe\Subscription::update($subscription->id, $params);
                    return $subscription;
                } catch(\Stripe\Error\Card $e) {
                    // Since it's a decline, \Stripe\Error\Card will be caught
                    $body = $e->getJsonBody();
                    $err  = $body['error'];

                    $flashBag->add('error', $err['message']);
                } catch (\Stripe\Error\RateLimit $e) {
                    // Too many requests made to the API too quickly
                    $error = $e;
                } catch (\Stripe\Error\InvalidRequest $e) {
                    // Invalid parameters were supplied to Stripe's API
                    $error = $e;
                } catch (\Stripe\Error\Authentication $e) {
                    // Authentication with Stripe's API failed
                    // (maybe you changed API keys recently)
                    $error = $e;
                } catch (\Stripe\Error\ApiConnection $e) {
                    // Network communication with Stripe failed
                    $error = $e;
                } catch (\Stripe\Error\Base $e) {
                    // Display a very generic error to the user, and maybe send
                    // yourself an email
                    $error = $e;
                } catch (\Exception $e) {
                    // Something else happened, completely unrelated to Stripe
                    $error = $e;
                    var_dump($e);
                }
            }
        }
        return null;
    }

    public function updateSubscriptionAfterPlanChange($subscription = null, $plan=null) {
        $flashBag = $this->getFlashBag();
        if ($plan instanceof \Stripe\Plan && $subscription instanceof \Stripe\Subscription) {
            if ($subscription) {
                try {
                    $subscription = \Stripe\Subscription::update($subscription->id, [
                        'cancel_at_period_end' => false,
                        'proration_date' => $subscription->current_period_end,
                        'items' => [
                            [
                                'id' => $subscription->items->data[0]->id,
                                'plan' => $plan->id,
                            ] ,
                        ],
                    ]);
                    return $subscription;
                } catch(\Stripe\Error\Card $e) {
                    // Since it's a decline, \Stripe\Error\Card will be caught
                    $body = $e->getJsonBody();
                    $err  = $body['error'];

                    $flashBag->add('error', $err['message']);
                } catch (\Stripe\Error\RateLimit $e) {
                    // Too many requests made to the API too quickly
                    $error = $e;
                } catch (\Stripe\Error\InvalidRequest $e) {
                    // Invalid parameters were supplied to Stripe's API
                    $error = $e;
                } catch (\Stripe\Error\Authentication $e) {
                    // Authentication with Stripe's API failed
                    // (maybe you changed API keys recently)
                    $error = $e;
                } catch (\Stripe\Error\ApiConnection $e) {
                    // Network communication with Stripe failed
                    $error = $e;
                } catch (\Stripe\Error\Base $e) {
                    // Display a very generic error to the user, and maybe send
                    // yourself an email
                    $error = $e;
                } catch (\Exception $e) {
                    // Something else happened, completely unrelated to Stripe
                    $error = $e;
                }
            }
        }
        return null;
    }

    public function retrieveSubscription(string $subscriptionId) {
        try {
            $subscription = \Stripe\Subscription::retrieve($subscriptionId);
            return $subscription;
        } catch(\Exception $exception) {

        }
        return null;
    }

    public function finalizeInvoice(string $invoiceId) {
        /** @var \Stripe\Invoice $invoice **/
        $invoice = $this->retrieveInvoice($invoiceId);

        try {
            $finalizedInvoice = $invoice->finalizeInvoice();
            return $finalizedInvoice;
        } catch(\Exception $exception) {

        }
        return false;
    }

    public function retrieveInvoice(string $invoiceId) {
        try {
            $invoice = \Stripe\Invoice::retrieve($invoiceId);
            return $invoice;
        } catch(\Exception $exception) {

        }
        return null;
    }

    public function paySubscription(string $subscriptionId) {
        $latestInvoiceId = $this->retrieveLatestInvoiceForSubscription($subscriptionId);
        if ($latestInvoiceId) {
            return $this->payInvoice($latestInvoiceId);
        }
        return false;
    }

    public function payInvoice(string $invoiceId) {
        $flashBag = $this->getFlashBag();
        /** @var \Stripe\Invoice $invoice **/


        try {
            $invoice = $this->retrieveInvoice($invoiceId);
            if ($invoice->paid == true) {
                return true;
            }
            return $invoice->pay();
        } catch(\Stripe\Error\Card $e) {
            // Since it's a decline, \Stripe\Error\Card will be caught
            $body = $e->getJsonBody();
            $err  = $body['error'];
            $flashBag->add('error', $err['message']);
        } catch (\Stripe\Error\RateLimit $e) {
            // Too many requests made to the API too quickly
        } catch (\Stripe\Error\InvalidRequest $e) {
            // Invalid parameters were supplied to Stripe's API
        } catch (\Stripe\Error\Authentication $e) {
            // Authentication with Stripe's API failed
            // (maybe you changed API keys recently)
        } catch (\Stripe\Error\ApiConnection $e) {
            // Network communication with Stripe failed
        } catch (\Stripe\Error\Base $e) {
            // Display a very generic error to the user, and maybe send
            // yourself an email
        } catch (\Exception $e) {
            // Something else happened, completely unrelated to Stripe
        }

        return false;
    }

    public function retrieveCustomerPaymentMethod(string $customerId) {
        $stripeCard = $this->retrieveCard($customerId);

        if ($stripeCard) {
            return $this->modelManager->convert($stripeCard);
        }
        return null;
    }

    public function retrieveCard(string $customerId) {
        /** @var \Stripe\Customer $customer */
        $customer = $this->retrieveCustomer($customerId);
        $sources = $customer->sources->data;

        if (count($sources) == 0) {
            return null;
        }

        try {
            return $sources[0];
        } catch(\Exception $exception) {

        }
        return null;
    }

    public function retrieveCustomer(string $customerId) {
        try {
            $customer = \Stripe\Customer::retrieve($customerId);
            return $customer;
        } catch(\Exception $exception) {
        }
        return null;
    }

    public function retrieveInvoices(string $customerId, int $limit = 24, ?string $afterInvoiceId = null) {
        $flashBag = $this->getFlashBag();

        if ($customerId == "" || $customerId == null) {
            return false;
        }

        $parameters = ['customer' => $customerId, 'limit' => $limit];
        if ($afterInvoiceId) {
            $parameters['starting_after'] = $afterInvoiceId;
        }
        try {
            $invoices = [];
            $stripeInvoices = \Stripe\Invoice::all($parameters);

            foreach ($stripeInvoices as $stripeInvoice) {
                $invoices[] = $this->modelManager->convert($stripeInvoice);
            }
            return $invoices;
        } catch(\Exception $exception) {
        }
        return false;
    }

    public function updateCustomerPaymentMethod(User $user, ?string $token) {
        if ($token == null) {
            return false;
        }

        $flashBag = $this->getFlashBag();
        if ($user->getCustomerId()) {
            try {
                $updatedCustomer = \Stripe\Customer::update(
                    $user->getCustomerId(),
                    [
                        'source' => $token,
                    ]
                );
                $success = "Your card details have been updated!";
                $flashBag->add('success', $success);
                return $updatedCustomer['id'];
            } catch(\Stripe\Error\Card $e) {
                $body = $e->getJsonBody();
                $err  = $body['error'];
                $error = $err['message'];

                $flashBag->add('error', $error);
            }
            return false;
        } else {
            $customer = $this->getCustomer($user, $token);
            return $customer != null;
        }
    }

    public function cancelSubscription(string $id) {
        /** @var \Stripe\Subscription $subscription */
        $subscription = $this->retrieveSubscription($id);
        if ($subscription) {
            $updatedSubscription = \Stripe\Subscription::update($id,
                [
                    'cancel_at_period_end' => true,
                ]
            );
//            if ($updatedSubscription) {
//                $this->modelManager->save($updatedSubscription);
//            }
            return true;
        }
        return false;
    }

    public function reactivateSubscription(User $user, string $id) {
        $flashBag = $this->getFlashBag();
        /** @var \Stripe\Subscription $subscription */
        $subscription = $this->retrieveSubscription($id);
        if ($subscription == null) {
            return false;
        }
        if ($subscription->status != 'canceled' && $subscription->cancel_at_period_end == true) {
            try {
                $subscription = \Stripe\Subscription::update($id,
                    [
                        'cancel_at_period_end' => false,
                    ]
                );
                return $subscription;
            } catch(\Stripe\Error\Card $e) {
                // Since it's a decline, \Stripe\Error\Card will be caught
                $body = $e->getJsonBody();
                $err  = $body['error'];

                $flashBag->add('error', $err['message']);
            } catch (\Stripe\Error\RateLimit $e) {
                // Too many requests made to the API too quickly
                $error = $e;
            } catch (\Stripe\Error\InvalidRequest $e) {
                // Invalid parameters were supplied to Stripe's API
                $error = $e;
            } catch (\Stripe\Error\Authentication $e) {
                // Authentication with Stripe's API failed
                // (maybe you changed API keys recently)
                $error = $e;
            } catch (\Stripe\Error\ApiConnection $e) {
                // Network communication with Stripe failed
                $error = $e;
            } catch (\Stripe\Error\Base $e) {
                // Display a very generic error to the user, and maybe send
                // yourself an email
                $error = $e;
            } catch (\Exception $e) {
                // Something else happened, completely unrelated to Stripe
                $error = $e;
            }
            return false;
        } else {
            return $this->createSubscription($user, $subscription->plan->id);
        }
    }

    public function createSubscription(User $user, string $plan) {
        $flashBag = $this->getFlashBag();
        $customer = $this->getCustomer($user, null);
        $metadata = ['user_id' => $user->getId(), 'fullname' => $user->getFullname()];
        if ($user instanceof User\CityUser) {
            $metadata['city'] = $user->getCity()->getName();
            $metadata['city_id'] = $user->getCity()->getId();
        }
        try {
            $subscription = \Stripe\Subscription::create([
                'customer' => $customer->id,
                'prorate' => true,
                'metadata' => $metadata,
                'items' => [
                    [
                        'plan' => $plan,
                    ],
                ],
            ]);
            return $subscription;
        } catch(\Stripe\Error\Card $e) {
            // Since it's a decline, \Stripe\Error\Card will be caught
            $body = $e->getJsonBody();
            $err  = $body['error'];
            $flashBag->add('error', $err['message']);
        } catch (\Stripe\Error\RateLimit $e) {
            // Too many requests made to the API too quickly
        } catch (\Stripe\Error\InvalidRequest $e) {
            // Invalid parameters were supplied to Stripe's API
        } catch (\Stripe\Error\Authentication $e) {
            // Authentication with Stripe's API failed
            // (maybe you changed API keys recently)
        } catch (\Stripe\Error\ApiConnection $e) {
            // Network communication with Stripe failed
        } catch (\Stripe\Error\Base $e) {
            // Display a very generic error to the user, and maybe send
            // yourself an email
        } catch (\Exception $e) {
            // Something else happened, completely unrelated to Stripe
        }
        return false;
    }

    public function deactivatePlan(string $planId) {
        $flashBag = $this->getFlashBag();
        $deactivated = false;
        if ($planId) {
            try {
                \Stripe\Plan::update($planId, [
                    'active' => false,
                ]);
            } catch(\Stripe\Error\Card $e) {
                // Since it's a decline, \Stripe\Error\Card will be caught
                $body = $e->getJsonBody();
                $err  = $body['error'];

                $flashBag->add('error', $err['message']);
            } catch (\Stripe\Error\RateLimit $e) {
                // Too many requests made to the API too quickly
            } catch (\Stripe\Error\InvalidRequest $e) {
                // Invalid parameters were supplied to Stripe's API
            } catch (\Stripe\Error\Authentication $e) {
                // Authentication with Stripe's API failed
                // (maybe you changed API keys recently)
            } catch (\Stripe\Error\ApiConnection $e) {
                // Network communication with Stripe failed
            } catch (\Stripe\Error\Base $e) {
                // Display a very generic error to the user, and maybe send
                // yourself an email
            } catch (\Exception $e) {
                // Something else happened, completely unrelated to Stripe
            }
        }
    }

    public function activatePlan(string $planId) {
        $flashBag = $this->getFlashBag();
        $deactivated = false;
        if ($planId) {
            try {
                \Stripe\Plan::update($planId, [
                    'active' => true,
                ]);
            } catch(\Stripe\Error\Card $e) {
                // Since it's a decline, \Stripe\Error\Card will be caught
                $body = $e->getJsonBody();
                $err  = $body['error'];

                $flashBag->add('error', $err['message']);
            } catch (\Stripe\Error\RateLimit $e) {
                // Too many requests made to the API too quickly
            } catch (\Stripe\Error\InvalidRequest $e) {
                // Invalid parameters were supplied to Stripe's API
            } catch (\Stripe\Error\Authentication $e) {
                // Authentication with Stripe's API failed
                // (maybe you changed API keys recently)
            } catch (\Stripe\Error\ApiConnection $e) {
                // Network communication with Stripe failed
            } catch (\Stripe\Error\Base $e) {
                // Display a very generic error to the user, and maybe send
                // yourself an email
            } catch (\Exception $e) {
                // Something else happened, completely unrelated to Stripe
            }
        }
    }

    public function getProduct(string $id) {
        /** @var \Stripe\Product $product */
        $product = null;

        try {
            $product = \Stripe\Product::retrieve($id);
        } catch (\Exception $exception) {
        }

        if (!$product) {
            try {
                $product = \Stripe\Product::create([
                    'id' => $id,
                    'name' => ucwords(str_replace('-', ' ', $id)),
                    'type' => 'service'
                ]);
            } catch (\Exception $exception) {
            }
        }

        return $product;
    }

    public function getCityPlan(SubscriptionPlan $subscriptionPlan, $product = null, City $city) {
        /** @var \Stripe\Plan $plan */
        $plan = null;

        if ($product instanceof \Stripe\Product) {
            $amount = $subscriptionPlan->getPrice();
            if ($subscriptionPlan instanceof SubscriptionPlan\CitySubscriptionPlan) {
                $amount = $subscriptionPlan->getPriceByFTE($city->getCountFTE());
            }

            $fte = '';
            $id = implode("-", [$product->id, $subscriptionPlan->getSlug()]);
            if ($subscriptionPlan->getRawStripePlan()) {
                $id = $subscriptionPlan->getRawStripePlan();
            }
            $metadata = ['subscription_plan_id' => $subscriptionPlan->getId()];
            $priceSchedule = $subscriptionPlan->getPriceScheduleByFTE($city->getCountFTE());
            if ($priceSchedule) {
                $fte = implode("-", [strval($priceSchedule->getMinCountOfFTEs()), strval($priceSchedule->getMaxCountOfFTEs())]);
                $id = implode("-", [$product->id, $subscriptionPlan->getSlug(), $fte]);
                $metadata['price_schedule_id'] = $priceSchedule->getId();
                if ($priceSchedule->getRawStripePlan()) {
                    $id = $priceSchedule->getRawStripePlan();
                }
            }

            try {
                $plan = \Stripe\Plan::retrieve($id);
            } catch(\Exception $exception) {

            }

            if (!$plan) {
                try {
                    $plan = \Stripe\Plan::create([
                        'id' => $id,
                        'currency' => 'usd',
                        'interval' => $subscriptionPlan->getRenewalFrequency()->determineInterval(),
                        'metadata' => $metadata,
                        'product' => $product->id,
                        'nickname' => $subscriptionPlan->getName(),
                        'amount' => $amount * 100,
                    ]);
                } catch(\Exception $exception) {
                }
            }
        }

        return $plan;
    }

    public function updateCityPlan(SubscriptionPlan $subscriptionPlan, City $city) {
        /** @var \Stripe\Plan $plan */
        $plan = null;

        $product_id = 'city-membership';
        $amount = $subscriptionPlan->getNextPrice();
        if ($subscriptionPlan instanceof SubscriptionPlan\CitySubscriptionPlan) {
            $amount = $subscriptionPlan->getNextPriceByFTE($city->getCountFTE());
        }

        $fte = '';
        $metadata = ['subscription_plan_id' => $subscriptionPlan->getId()];
        $id = implode("-", [$product_id, $subscriptionPlan->getSlug(), $subscriptionPlan->getNextPriceEffectiveDateByFTE($city->getCountFTE())->format('Y-m-d')]);
        $priceSchedule = $subscriptionPlan->getPriceScheduleByFTE($city->getCountFTE());
        if ($priceSchedule) {
            $metadata['price_schedule_id'] = $priceSchedule->getId();
            $fte = implode("-", [strval($priceSchedule->getMinCountOfFTEs()), strval($priceSchedule->getMaxCountOfFTEs())]);
            $id = implode("-", [$product_id, $subscriptionPlan->getSlug(), $fte, $subscriptionPlan->getNextPriceEffectiveDateByFTE($city->getCountFTE())->format('Y-m-d')]);
        }

        try {
            $plan = \Stripe\Plan::retrieve($id);
        } catch(\Exception $exception) {

        }

        if (!$plan) {
            try {
                $plan = \Stripe\Plan::create([
                    'id' => $id,
                    'currency' => 'usd',
                    'interval' => $subscriptionPlan->getRenewalFrequency()->determineInterval(),
                    'metadata' => $metadata,
                    'product' => $product_id,
                    'nickname' => $subscriptionPlan->getName() . ' Updated on ' . $subscriptionPlan->getNextPriceEffectiveDateByFTE($city->getCountFTE())->format('Y-m-d'),
                    'amount' => $amount * 100,
                ]);
            } catch(\Exception $exception) {
            }
        }

        return $plan;
    }

    public function getPlan(SubscriptionPlan $subscriptionPlan,  $product = null) {
        /** @var \Stripe\Plan $plan */
        $plan = null;

        if ($product instanceof \Stripe\Product) {
            $id = $product->id.'-'.$subscriptionPlan->getSlug();
            if ($subscriptionPlan->getRawStripePlan()) {
                $id = $subscriptionPlan->getRawStripePlan();
            }
            try {
                $plan = \Stripe\Plan::retrieve($id);
            } catch(\Exception $exception) {

            }

            if (!$plan) {
                try {
                    $plan = \Stripe\Plan::create([
                        'id' => $id,
                        'currency' => 'usd',
                        'interval' => $subscriptionPlan->getRenewalFrequency()->determineInterval(),
                        'metadata' => [
                            'subscription_plan_id' => $subscriptionPlan->getId(),
                        ],
                        'product' => $product->id,
                        'nickname' => $subscriptionPlan->getName(),
                        'amount' => $subscriptionPlan->getPrice() * 100,
                    ]);
                } catch(\Exception $exception) {
                }
            }
        }

        return $plan;
    }

    public function updatePlan(SubscriptionPlan $subscriptionPlan) {
        /** @var \Stripe\Plan $plan */
        $plan = null;

        $product_id = 'job-seeker-membership';
        $id = $product_id.'-'.$subscriptionPlan->getSlug().'-'.$subscriptionPlan->getNextPriceEffectiveDate()->format('Y-m-d');

        try {
            $plan = \Stripe\Plan::retrieve($id);
        } catch(\Exception $exception) {

        }

        if (!$plan) {
            try {
                $plan = \Stripe\Plan::create([
                    'id' => $id,
                    'currency' => 'usd',
                    'interval' => $subscriptionPlan->getRenewalFrequency()->determineInterval(),
                    'metadata' => [
                        'subscription_plan_id' => $subscriptionPlan->getId(),
                    ],
                    'product' => $product_id,
                    'nickname' => $subscriptionPlan->getName() . ' Updated on ' . $subscriptionPlan->getNextPriceEffectiveDate()->format('m-d-Y'),
                    'amount' => $subscriptionPlan->getNextPrice() * 100,
                ]);
            } catch(\Exception $exception) {
            }
        }

        return $plan;
    }


    public function getCustomer(User $user, ?string $token) {
        $flashBag = $this->getFlashBag();
        /** @var \Stripe\Customer $customer */
        $customer = null;

        if ($user->getStripeCustomer()) {
            try {
                $customer = \Stripe\Customer::retrieve($user->getStripeCustomer()->getStripeId());
            } catch(\Exception $exception) {

            }
        }

        $metadata = ['user_id' => $user->getId(), 'fullname' => $user->getFullname()];
        if ($user instanceof User\CityUser) {
            $metadata['city'] = $user->getCity()->getName();
            $metadata['city_id'] = $user->getCity()->getId();
        }

        if (!$customer && $token) {
            try {
                $customer = \Stripe\Customer::create([
                    'email' => $user->getEmail(),
                    'metadata' => $metadata,
                    'source' => $token,
                ]);
            } catch(\Stripe\Error\Card $e) {
            // Since it's a decline, \Stripe\Error\Card will be caught
            $body = $e->getJsonBody();
            $err  = $body['error'];
            $flashBag->add('error', $err['message']);
            } catch (\Stripe\Error\RateLimit $e) {
                // Too many requests made to the API too quickly
            } catch (\Stripe\Error\InvalidRequest $e) {
                // Invalid parameters were supplied to Stripe's API
            } catch (\Stripe\Error\Authentication $e) {
                // Authentication with Stripe's API failed
                // (maybe you changed API keys recently)
            } catch (\Stripe\Error\ApiConnection $e) {
                // Network communication with Stripe failed
            } catch (\Stripe\Error\Base $e) {
                // Display a very generic error to the user, and maybe send
                // yourself an email
            } catch (\Exception $e) {
                // Something else happened, completely unrelated to Stripe
            }
        }

        return $customer;
    }

    public function updateSubscriptionItem($subscriptionItemId, ?\Stripe\Plan $plan) {
        $flashBag = $this->getFlashBag();
        try {
            \Stripe\SubscriptionItem::update($subscriptionItemId, [
                'plan' => $plan->id,
            ]);
            return true;
        } catch(\Stripe\Error\Card $e) {
            // Since it's a decline, \Stripe\Error\Card will be caught
            $body = $e->getJsonBody();
            $err  = $body['error'];

            $flashBag->add('error', $err['message']);
        } catch (\Stripe\Error\RateLimit $e) {
            // Too many requests made to the API too quickly
        } catch (\Stripe\Error\InvalidRequest $e) {
            // Invalid parameters were supplied to Stripe's API
        } catch (\Stripe\Error\Authentication $e) {
            // Authentication with Stripe's API failed
            // (maybe you changed API keys recently)
        } catch (\Stripe\Error\ApiConnection $e) {
            // Network communication with Stripe failed
        } catch (\Stripe\Error\Base $e) {
            // Display a very generic error to the user, and maybe send
            // yourself an email
        } catch (\Exception $e) {
            // Something else happened, completely unrelated to Stripe
        }
        return false;
    }

    public function getSubscription(User $user, $customer = null, $plan = null) {
        $flashBag = $this->getFlashBag();
        /** @var \Stripe\Subscription $subscription */
        $subscription = null;


        if ($plan instanceof \Stripe\Plan && $customer instanceof \Stripe\Customer ) {
            $id = null;
            if ($user instanceof User\JobSeekerUser) {
                if ($user->getSubscription() && $user->getSubscription()->getStripeSubscription())
                    try {
                        $subscription = \Stripe\Subscription::retrieve($user->getSubscription()->getStripeSubscription()->getStripeId());
                    } catch(\Exception $exception) {

                    }
            } else if ($user instanceof User\CityUser) {
                $city = $user->getCity();
                if ($city->getSubscription() && $city->getSubscription()->getStripeSubscription())
                    try {
                        $subscription = \Stripe\Subscription::retrieve($city->getSubscription()->getStripeSubscription()->getStripeId());
                    } catch(\Exception $exception) {

                    }
            }

            if ($subscription && $subscription->status == 'canceled') {
                $subscription = null;
            }

            $metadata = ['user_id' => $user->getId(), 'fullname' => $user->getFullname()];

            if ($user instanceof User\CityUser) {
                $metadata['city'] = $user->getCity()->getName();
                $metadata['city_id'] = $user->getCity()->getId();
            }
            if (!$subscription) {
                $params = [
                    'customer' => $customer->id,
                    'prorate' => true,
                    'metadata' => $metadata,
                    'items' => [
                        [
                            'plan' => $plan->id,
                        ],
                    ],
                ];

                try {
                    $subscription = \Stripe\Subscription::create($params);
                } catch(\Stripe\Error\Card $e) {
                    // Since it's a decline, \Stripe\Error\Card will be caught
                    $body = $e->getJsonBody();
                    $err  = $body['error'];
                    $flashBag->add('error', $err['message']);
                } catch (\Stripe\Error\RateLimit $e) {
                    // Too many requests made to the API too quickly
                } catch (\Stripe\Error\InvalidRequest $e) {
                    // Invalid parameters were supplied to Stripe's API
                } catch (\Stripe\Error\Authentication $e) {
                    // Authentication with Stripe's API failed
                    // (maybe you changed API keys recently)
                } catch (\Stripe\Error\ApiConnection $e) {
                    // Network communication with Stripe failed
                } catch (\Stripe\Error\Base $e) {
                    // Display a very generic error to the user, and maybe send
                    // yourself an email
                } catch (\Exception $e) {
                    // Something else happened, completely unrelated to Stripe
                }
            } else {
                // Has been created let's update
                // If error than we want to alert the user that there subscription wasn't updated
                $subscription = $this->updateSubscription($user, $subscription, $plan);
            }
        }

        return $subscription;
    }

    public function previewUpcomingInvoice(User $user, $subscription, SubscriptionPlan $subscriptionPlan, string $productId) {
        $error = false;
        $flashBag = $this->getFlashBag();
        /** @var User\CityUser $user */
        /** @var \Stripe\Subscription $subscription */
        $subscription = $this->retrieveSubscription($subscription);
        $customer = $this->getCustomer($user, null);
        $product = $this->getProduct($productId);

        if ($subscriptionPlan instanceof SubscriptionPlan\CitySubscriptionPlan) {
            $plan = $this->getCityPlan($subscriptionPlan, $product, $user->getCity());
        } else {
            $plan = $this->getPlan($subscriptionPlan, $product);
        }


        if ($plan instanceof \Stripe\Plan && $subscription instanceof \Stripe\Subscription) {
            // Proration should be midnight UTC of date
            $prorationDate = new \DateTime();

            try {
                $upcoming = \Stripe\Invoice::upcoming([
                    'customer' => $customer->id,
                    'subscription' => $subscription->id,
                    'subscription_items' => [
                        [
                            'id' => $subscription->items->data[0]->id,
                            'plan' => $plan->id,
                        ],
                    ],
//                    'subscription_proration_date' => $prorationDate->getTimestamp()
                ]);
                return $upcoming;
            } catch (\Exception $exception) {
                $error = null;
            }
        }

        return $error;
    }
}