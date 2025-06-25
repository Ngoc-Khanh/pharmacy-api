<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;
    
    protected $connection = 'mongodb';
    protected $collection = 'suppliers';
    protected $fillable = [
        'name',
        'address',
        'contact_phone',
        'contact_email',
        'created_at',
    ];

    public function medicines()
    {
        return $this->hasMany(Medicine::class, "supplier_id");
    }
}
