<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Order",
 *     title="Order",
 *     description="Order model",
 *     @OA\Property(property="_id", type="string", example="60f1a5b0e5a4d12345678905"),
 *     @OA\Property(property="user_id", type="string", example="60f1a5b0e5a4d12345678900"),
 *     @OA\Property(property="order_number", type="string", example="ORD-20250428-001"),
 *     @OA\Property(property="status", type="string", enum={"pending", "processing", "shipped", "delivered", "cancelled"}, example="pending"),
 *     @OA\Property(
 *         property="items",
 *         type="array",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="medicine_id", type="string", example="60f1a5b0e5a4d12345678901"),
 *             @OA\Property(property="name", type="string", example="Paracetamol 500mg"),
 *             @OA\Property(property="variant", type="string", example="Box of 10 tablets"),
 *             @OA\Property(property="quantity", type="integer", example=2),
 *             @OA\Property(property="price", type="number", format="float", example=25000),
 *             @OA\Property(property="discount_price", type="number", format="float", example=22500),
 *             @OA\Property(property="total", type="number", format="float", example=45000)
 *         )
 *     ),
 *     @OA\Property(property="subtotal", type="number", format="float", example=45000),
 *     @OA\Property(property="shipping_fee", type="number", format="float", example=15000),
 *     @OA\Property(property="total", type="number", format="float", example=60000),
 *     @OA\Property(property="payment_method", type="string", enum={"cash", "credit_card", "momo", "zalopay"}, example="cash"),
 *     @OA\Property(
 *         property="shipping_address",
 *         ref="#/components/schemas/UserAddress"
 *     ),
 *     @OA\Property(property="note", type="string", example="Please deliver after 6pm"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-28T12:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-04-28T12:00:00Z")
 * )
 * 
 * @OA\Schema(
 *     schema="OrderDetail",
 *     title="Order Detail",
 *     description="Detailed Order information",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/Order"),
 *         @OA\Schema(
 *             @OA\Property(
 *                 property="user",
 *                 ref="#/components/schemas/User"
 *             ),
 *             @OA\Property(
 *                 property="tracking",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="status", type="string", example="order_placed"),
 *                     @OA\Property(property="timestamp", type="string", format="date-time", example="2025-04-28T12:00:00Z"),
 *                     @OA\Property(property="comment", type="string", example="Order has been placed")
 *                 )
 *             )
 *         )
 *     }
 * )
 */
class Order extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'orders';
    protected $fillable = [
        'user_id',
        'order_number',
        'status',
        'items',
        'subtotal',
        'shipping_fee',
        'total',
        'payment_method',
        'shipping_address',
        'note',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
