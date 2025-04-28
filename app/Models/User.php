<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *     schema="User",
 *     title="User",
 *     description="User model",
 *     @OA\Property(property="_id", type="string", example="60f1a5b0e5a4d12345678900"),
 *     @OA\Property(property="username", type="string", example="johndoe"),
 *     @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
 *     @OA\Property(property="first_name", type="string", example="John"),
 *     @OA\Property(property="last_name", type="string", example="Doe"),
 *     @OA\Property(property="phone", type="string", example="+84123456789"),
 *     @OA\Property(property="role", type="string", enum={"admin", "pharmacist", "customer"}, example="customer"),
 *     @OA\Property(property="status", type="string", enum={"active", "inactive", "banned"}, example="active"),
 *     @OA\Property(
 *         property="profile_image",
 *         type="object",
 *         @OA\Property(property="public_id", type="string", example="profiles/abcdef123456"),
 *         @OA\Property(property="url", type="string", format="uri", example="https://res.cloudinary.com/demo/image/upload/profiles/abcdef123456.jpg")
 *     ),
 *     @OA\Property(property="dob", type="string", format="date-time", example="1990-01-01T00:00:00Z"),
 *     @OA\Property(property="gender", type="string", enum={"male", "female", "other"}, example="male"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2023-01-01T12:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-01-01T12:00:00Z"),
 *     @OA\Property(property="last_login_at", type="string", format="date-time", example="2023-01-01T12:00:00Z")
 * )
 * 
 * @OA\Schema(
 *     schema="UserAddress",
 *     title="User Address",
 *     description="User address information",
 *     @OA\Property(property="id", type="string", example="60f1a5b0e5a4d12345678910"),
 *     @OA\Property(property="name", type="string", example="Home"),
 *     @OA\Property(property="phone", type="string", example="+84123456789"),
 *     @OA\Property(property="address_line1", type="string", example="123 Main Street"),
 *     @OA\Property(property="address_line2", type="string", example="Apartment 4B"),
 *     @OA\Property(property="city", type="string", example="Ho Chi Minh City"),
 *     @OA\Property(property="state", type="string", example=""),
 *     @OA\Property(property="country", type="string", example="Vietnam"),
 *     @OA\Property(property="postal_code", type="string", example="70000"),
 *     @OA\Property(property="is_default", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2023-01-01T12:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-01-01T12:00:00Z")
 * )
 * 
 * @OA\Schema(
 *     schema="UserDetail",
 *     title="User Detail",
 *     description="Detailed User information",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/User"),
 *         @OA\Schema(
 *             @OA\Property(
 *                 property="addresses",
 *                 type="array",
 *                 @OA\Items(ref="#/components/schemas/UserAddress")
 *             ),
 *             @OA\Property(
 *                 property="preferences",
 *                 type="object",
 *                 @OA\Property(property="language", type="string", example="en"),
 *                 @OA\Property(property="notification_preferences", type="object")
 *             ),
 *             @OA\Property(
 *                 property="social_accounts",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="provider", type="string", example="google"),
 *                     @OA\Property(property="provider_id", type="string", example="123456789"),
 *                     @OA\Property(property="created_at", type="string", format="date-time")
 *                 )
 *             )
 *         )
 *     }
 * )
 */
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

    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id');
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
