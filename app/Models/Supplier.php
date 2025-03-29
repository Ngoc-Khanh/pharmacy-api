<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

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
