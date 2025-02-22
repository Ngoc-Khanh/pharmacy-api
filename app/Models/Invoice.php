<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Invoice extends Model
{
    protected $connection = 'mongodb';
    protected $collection = "invoices";
    protected $fillable = [
        '_id',
        'order_id',
        'user_id',
        'invoice_number',
        'payment_method',
        'payment_status',
        'issued_date',
        'due_date',
        'note',
        'created_at',
        'details',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', '_id');
    }
}
