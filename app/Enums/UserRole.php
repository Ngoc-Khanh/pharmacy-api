<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'ADMIN';
    case PHARMACIST = 'PHARMACIST';
    case CUSTOMER = 'CUSTOMER';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Quản trị viên',
            self::PHARMACIST => 'Dược sĩ',
            self::CUSTOMER => 'Khách hàng',
        };
    }
}
