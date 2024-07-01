<?php

namespace App\Utils;

class NumberGenerator
{
    public function generateDocumentNumber($lastNumber, $prefix): string
    {
        $year = date('Y');
        $month = date('m');
        $nextNumber = $lastNumber ? $lastNumber + 1 : 1;

        return $prefix . $year . $month . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }
}