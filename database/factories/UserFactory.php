<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    protected $model = \App\Models\User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = $this->faker->firstName();
        $lastName = $this->faker->lastName();
        $username = $this->faker->unique()->userName();
        
        return [
            'firstname' => $firstName,
            'lastname' => $lastName,
            'username' => $username,
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('123456'),
            'phone' => '+84' . $this->faker->numberBetween(10000000, 99999999),
            'profile_image' => [
                'public_id' => null,
                'url' => './avatars/' . $this->faker->numberBetween(1, 8) . '.jpg',
                'alt' => $username . '-alt'
            ],
            'status' => $this->faker->randomElement([
                UserStatus::ACTIVE->value,
                UserStatus::PENDING->value,
                UserStatus::SUSPENDED->value
            ]),
            'role' => $this->faker->randomElement([
                UserRole::PHARMACIST->value,
                UserRole::CUSTOMER->value
            ]),
            'addresses' => [
                [
                    'name' => $firstName . ' ' . $lastName,
                    'phone' => '0' . $this->faker->numberBetween(100000000, 999999999),
                    'address_line1' => $this->faker->streetAddress(),
                    'address_line2' => $this->faker->secondaryAddress(),
                    'city' => $this->faker->randomElement([
                        'Hà Nội', 'TP.HCM', 'Đà Nẵng', 'Hải Phòng', 'Cần Thơ', 
                        'Huế', 'Nha Trang', 'Vũng Tàu', 'Quy Nhon', 'Nam Định'
                    ]),
                    'state' => $this->faker->randomElement([
                        'Hà Nội', 'TP.HCM', 'Đà Nẵng', 'Hải Phòng', 'Cần Thơ',
                        'Thừa Thiên Huế', 'Khánh Hòa', 'Bà Rịa - Vũng Tàu', 'Bình Định', 'Nam Định'
                    ]),
                    'country' => 'Vietnam',
                    'postal_code' => $this->faker->postcode(),
                    'is_default' => true,
                    '_id' => Str::uuid()->toString()
                ]
            ],
            'email_verified_at' => now(),
            'last_login_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
            'status' => UserStatus::PENDING->value,
        ]);
    }

    /**
     * Create a customer user.
     */
    public function customer(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => UserRole::CUSTOMER->value,
        ]);
    }

    /**
     * Create a pharmacist user.
     */
    public function pharmacist(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => UserRole::PHARMACIST->value,
        ]);
    }

    /**
     * Create an admin user.
     */
    public function admin(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => UserRole::ADMIN->value,
        ]);
    }

    /**
     * Create user with multiple addresses.
     */
    public function withMultipleAddresses(): static
    {
        return $this->state(function (array $attributes) {
            $firstName = $attributes['firstname'];
            $lastName = $attributes['lastname'];
            
            return [
                'addresses' => [
                    [
                        'name' => $firstName . ' ' . $lastName,
                        'phone' => '0' . $this->faker->numberBetween(100000000, 999999999),
                        'address_line1' => $this->faker->streetAddress(),
                        'address_line2' => $this->faker->secondaryAddress(),
                        'city' => 'Hà Nội',
                        'state' => 'Hà Nội',
                        'country' => 'Vietnam',
                        'postal_code' => '100000',
                        'is_default' => true,
                        '_id' => Str::uuid()->toString()
                    ],
                    [
                        'name' => $firstName . ' ' . $lastName,
                        'phone' => '0' . $this->faker->numberBetween(100000000, 999999999),
                        'address_line1' => $this->faker->streetAddress(),
                        'address_line2' => $this->faker->secondaryAddress(),
                        'city' => 'TP.HCM',
                        'state' => 'TP.HCM',
                        'country' => 'Vietnam',
                        'postal_code' => '700000',
                        'is_default' => false,
                        '_id' => Str::uuid()->toString()
                    ]
                ]
            ];
        });
    }
}
