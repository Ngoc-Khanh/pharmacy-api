<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends UserModel implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $connection = 'mongodb';
    protected $collection = 'users';

    protected $fillable = [
        'username',
        'email',
        'password',
        'first_name',
        'last_name',
        'phone',
        'addresses',
        'role',
        'profile_image',
        'dob',
        'gender',
        'social_accounts',
        'preferences',
        'status',
        'email_verified_at',
        'created_at',
        'updated_at',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'created_at'        => 'datetime',
            'updated_at'        => 'datetime',
            'last_login_at'     => 'datetime',
            'dob'               => 'datetime',
            'email_verified_at' => 'datetime',
            // 'addresses'         => 'array',
            // 'social_accounts'   => 'array',
            // 'preferences'       => 'array',
            // 'password'          => 'hashed',
        ];
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;
        $this->save();
        return $this;
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
