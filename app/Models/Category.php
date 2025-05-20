<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Category extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'categories';
    protected $fillable = [
        'title',
        'slug',
        'description',
        'is_active',
        'created_at',
    ];

    public function medicines()
    {
        return $this->hasMany(Medicine::class, "category_id");
    }
}
