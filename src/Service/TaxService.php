<?php

namespace App\Service;

class TaxService
{
    const TVA_RATE = 0.2;

    public function applyTva(float $priceWithoutTva): float
    {
        return $priceWithoutTva * (1 + self::TVA_RATE);
    }

    public function calculateTva(float $priceWithoutTva): float
    {
        return $priceWithoutTva * self::TVA_RATE;
    }
}
