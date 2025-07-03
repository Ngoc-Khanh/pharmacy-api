<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Auth\UserAuthenticatable as Authenticatable;
use MongoDB\Laravel\Eloquent\SoftDeletes;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $connection = 'mongodb';
    protected $collection = 'users';

    protected $fillable = [
        'username',
        'email',
        'password',
        'firstname',
        'lastname',
        'phone',
        'profile_image',
        'role',
        'status',
        'addresses',
        'email_verified_at',
        'verification_code',
        'verification_code_expires_at',
        'password_reset_token',
        'password_reset_token_expires_at',
        'created_at',
        'deleted_at',
    ];

    protected $hidden = [
        'password',
        'verification_code',
        'pasword_reset_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'status' => UserStatus::class,
            'verification_code_expires_at' => 'datetime',
            'password_reset_token_expires_at', 'datetime',
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

    public function hasVerifiedEmail()
    {
        return !is_null($this->email_verified_at);
    }

    public function markEmailAsVerified()
    {
        $this->email_verified_at = now();
        $this->status = UserStatus::ACTIVE->value;
        $this->unset(['verification_code', 'verification_code_expires_at']);
        $this->save();
    }

    public function getEmailForVerification()
    {
        return $this->email;
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'user_id');
    }

    public function cart()
    {
        return $this->hasOne(Cart::class, 'user_id');
    }
}
