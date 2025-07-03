<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;
    
    protected $connection = 'mongodb';
    protected $collection = 'orders';

    protected $fillable = [
        'user_id',
        'status',
        'items',
        'sub_total',
        'shipping_fee',
        'discount',
        'total_price',
        'shipping_address',
        'payment_method',
        'created_at',
        'deleted_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
