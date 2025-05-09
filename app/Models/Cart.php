<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Cart extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'carts';
    protected $fillable = [
        'user_id',
        'items',
        'updated_at',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
