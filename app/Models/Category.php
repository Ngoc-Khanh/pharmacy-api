<?php

namespace App\Models;

use Illuminate\Support\Str;
use MongoDB\Laravel\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Category",
 *     title="Category",
 *     description="Category model",
 *     @OA\Property(property="_id", type="string", example="60f1a5b0e5a4d12345678901"),
 *     @OA\Property(property="name", type="string", example="Antibiotics"),
 *     @OA\Property(property="slug", type="string", example="antibiotics"),
 *     @OA\Property(property="description", type="string", example="Medications that destroy or slow down the growth of bacteria")
 * )
 */
class Category extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'categories';

    protected $fillable = [
        'name',
        'description',
        'slug',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($category) {
            if (empty($category->slug)) $category->slug = Str::slug($category->name);
        });
    }

    public function medicines()
    {
        return $this->hasMany(Medicine::class, "category_id");
    }
}
