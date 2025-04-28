<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @OA\Schema(
 *     schema="Medicine",
 *     title="Medicine",
 *     description="Medicine model",
 *     @OA\Property(property="_id", type="string", example="60f1a5b0e5a4d12345678901"),
 *     @OA\Property(property="name", type="string", example="Paracetamol 500mg"),
 *     @OA\Property(property="slug", type="string", example="paracetamol-500mg"),
 *     @OA\Property(property="priority", type="integer", example=1),
 *     @OA\Property(property="thumbnail", type="string", example="https://example.com/image.jpg"),
 *     @OA\Property(property="description", type="string", example="Pain reliever and fever reducer"),
 *     @OA\Property(
 *         property="ratings",
 *         type="object",
 *         @OA\Property(property="liked", type="integer", example=15),
 *         @OA\Property(property="disliked", type="integer", example=2)
 *     ),
 *     @OA\Property(
 *         property="category",
 *         ref="#/components/schemas/Category"
 *     ),
 *     @OA\Property(
 *         property="supplier",
 *         ref="#/components/schemas/Supplier"
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="MedicineDetail",
 *     title="Medicine Detail",
 *     description="Detailed Medicine information",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/Medicine"),
 *         @OA\Schema(
 *             @OA\Property(
 *                 property="details",
 *                 type="object",
 *                 @OA\Property(property="ingredients", type="string", example="Paracetamol 500mg"),
 *                 @OA\Property(property="sideEffects", type="string", example="Nausea, vomiting, abdominal pain"),
 *                 @OA\Property(property="contraindications", type="string", example="Liver disease, alcohol consumption")
 *             ),
 *             @OA\Property(
 *                 property="usageguide",
 *                 type="object",
 *                 @OA\Property(property="dosage", type="string", example="1-2 tablets every 4-6 hours"),
 *                 @OA\Property(property="method", type="string", example="Oral administration"),
 *                 @OA\Property(property="notes", type="string", example="Do not exceed 8 tablets in 24 hours")
 *             ),
 *             @OA\Property(
 *                 property="variants",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="name", type="string", example="Box of 10 tablets"),
 *                     @OA\Property(property="price", type="number", format="float", example=25000),
 *                     @OA\Property(property="discount_price", type="number", format="float", example=22500),
 *                     @OA\Property(property="stock", type="integer", example=100)
 *                 )
 *             )
 *         )
 *     }
 * )
 */
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
        'details',
        'usageguide',
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
