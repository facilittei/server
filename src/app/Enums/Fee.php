<?php

namespace App\Enums;

enum Fee
{
    case PERCENTAGE;
    case TRANSACTION;

    public function total(): int|float
    {
        return match($this)
        {
            self::PERCENTAGE => 5,
            self::TRANSACTION => 0.5,
        };
    }
}