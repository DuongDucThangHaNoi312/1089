<?php

namespace App\Entity;

interface CardInterface {
    public function getBrand();
    public function getLast4();
    public function getExpMonth();
    public function getExpYear();
}