<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $connection = 'mongodb';
    protected $collection = 'invoices';
    protected $fillable = [
        'order_id',
        'user_id',
        'invoice_number',
        'items',
        'total_price',
        'payment_method',
        'issued_at',
        'status',
        'created_at',
        'deleted_at',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
