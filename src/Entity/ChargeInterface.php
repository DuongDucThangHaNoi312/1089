<?php

namespace App\Entity;

interface ChargeInterface {
    public function getStatus();
    public function hasFailed();
    public function getCustomer();
    public function getSource();
    public function getAmount();
    public function getCreated();
}