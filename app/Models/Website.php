<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Website extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'websites';

    protected $fillable = [
        'name',
        'url',
        'description',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
