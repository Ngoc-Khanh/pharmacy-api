<?php

namespace App\Enums;

enum MedicineStatus: string
{
    case IN_STOCK = "IN-STOCK";
    case OUT_OF_STOCK = "OUT-OF-STOCK";
    case PRE_ORDER = "PRE-ORDER";

    public function toString(): string
    {
        return match ($this) {
            self::IN_STOCK => 'Còn hàng',
            self::OUT_OF_STOCK => 'Hết hàng',
            self::PRE_ORDER => 'Đặt hàng trước',
        };
    }
}
