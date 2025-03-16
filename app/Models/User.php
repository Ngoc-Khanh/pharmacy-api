<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class User extends UserModel implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $connection = 'mongodb';
    protected $collection = 'users';

    protected $fillable = [
        'username',
        'email',
        'password',
        'full_name',
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
            'create_at' => 'datetime',
            'update_at' => 'datetime',
            'last_login_at' => 'datetime',
            'dob' => 'datetime',
            'addresses' => 'array',
            'socialAccounts' => 'array',
            'preferences' => 'array',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
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
