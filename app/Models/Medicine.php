<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Support\Str;

class Medicine extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'medicines';

    protected $fillable = [
        'category_id',
        'supplier_id',
        'name',
        'slug',
        'priority',
        'thumbnail',
        'description',
        'variants',
        'ratings',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($medicine) {
            if (empty($medicine->slug)) $medicine->slug = Str::slug($medicine->name);
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class, "category_id");
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, "supplier_id");
    }
}
