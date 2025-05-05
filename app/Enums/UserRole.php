<?php

namespace App\Emums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case PHARMACIST = 'pharmacist';
    case CUSTOMER = 'customer';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Quản trị viên',
            self::PHARMACIST => 'Dược sĩ',
            self::CUSTOMER => 'Khách hàng',
        };
    }
}
