<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Supplier",
 *     title="Supplier",
 *     description="Supplier model",
 *     @OA\Property(property="_id", type="string", example="60f1a5b0e5a4d12345678902"),
 *     @OA\Property(property="name", type="string", example="Pharmanova Ltd"),
 *     @OA\Property(property="contact_email", type="string", example="contact@pharmanova.com"),
 *     @OA\Property(property="contact_phone", type="string", example="+84123456789"),
 *     @OA\Property(property="address", type="string", example="123 Pharmaceutical Street, District 1, HCMC")
 * )
 */
class Supplier extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'suppliers';

    protected $fillable = [
        'name',
        'contact_email',
        'contact_phone',
        'address',
    ];

    public function medicines()
    {
        return $this->hasMany(Medicine::class, "supplier_id");
    }
}
