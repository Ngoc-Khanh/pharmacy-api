<?php

namespace App\Enums;

enum MedicineStatus
{
    case IN_STOCK;
    case OUT_OF_STOCK;
    case PRE_ORDER;

    public function toString(): string
    {
        return match ($this) {
            self::IN_STOCK => 'IN-STOCK',
            self::OUT_OF_STOCK => 'OUT-OF-STOCK',
            self::PRE_ORDER => 'PRE-ORDER',
        };
    }
}
