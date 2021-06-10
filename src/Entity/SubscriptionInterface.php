<?php

namespace App\Entity;

interface SubscriptionInterface {
    public function getStatus();
    public function getPaymentProcessorId();
    public function getCurrentPeriodEnd();
}