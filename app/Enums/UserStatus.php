<?php

namespace App\Enums;

enum UserStatus: string
{
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended'; 
    case PENDING = 'pending';
    
    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Đang hoạt động',
            self::SUSPENDED => 'Đã bị khóa',
            self::PENDING => 'Đang chờ duyệt',
        };
    }
}
