<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Review extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'reviews';
    protected $fillable = [
        'user_id',
        'medicine_id',
        'rating',
        'comment',
        'created_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function medicine()
    {
        return $this->belongsTo(Medicine::class, 'medicine_id');
    }
}
