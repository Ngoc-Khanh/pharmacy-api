<?php

namespace App\Models;

use Illuminate\Support\Str;
use MongoDB\Laravel\Eloquent\Model;

class Medicine extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'medicines';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'category_id',
        'supplier_id',
        'name',
        'slug',
        'thumbnail',
        'description',
        'variants',
        'ratings',
        'details',
        'usageguide',
        'created_by',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (!$model->_id) $model->_id = (string) Str::uuid();
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
}
