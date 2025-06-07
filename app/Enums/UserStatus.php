<?php

namespace App\Enums;

enum UserStatus: string
{
    case ACTIVE = 'ACTIVE';
    case SUSPENDED = 'SUSPENDED'; 
    case PENDING = 'PENDING';
    
    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Đang hoạt động',
            self::SUSPENDED => 'Đã bị khóa',
            self::PENDING => 'Đang chờ duyệt',
        };
    }
}
