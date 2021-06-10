<?php

namespace App\Entity;

interface InvoiceInterface {
    public function getTotal();
    public function getNumber();
    public function getDate();
    public function getAmountDue();
    public function getStartingBalance();
    public function getPeriodStart();
    public function getPeriodEnd();
    public function isPaid();
    public function isAttempted();
}