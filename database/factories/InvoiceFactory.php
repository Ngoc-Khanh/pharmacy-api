<?php

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = \App\Models\Invoice::class;
    
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $itemCount = $this->faker->numberBetween(1, 5);
        $items = [];
        $totalPrice = 0;
        
        // Tạo danh sách items ngẫu nhiên
        for ($i = 0; $i < $itemCount; $i++) {
            $quantity = $this->faker->numberBetween(1, 3);
            $price = $this->faker->numberBetween(10000, 100000);
            $itemTotal = $quantity * $price;
            $totalPrice += $itemTotal;
            
            $items[] = [
                'medicine_id' => $this->faker->uuid(),
                'quantity' => $quantity,
                'price' => $price,
                'item_total' => $itemTotal
            ];
        }
        
        $issuedAt = $this->faker->dateTimeBetween('-30 days', 'now');
        
        return [
            'order_id' => (string) $this->faker->objectId(),
            'user_id' => (string) $this->faker->objectId(),
            'invoice_number' => 'INV-' . $issuedAt->format('Ymd') . '-' . str_pad($this->faker->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'items' => $items,
            'total_price' => $totalPrice,
            'payment_method' => $this->faker->randomElement(['COD', 'BANK_TRANSFER', 'CREDIT_CARD', 'MOMO', 'ZALOPAY']),
            'issued_at' => $issuedAt->format('Y-m-d\TH:i:s.v\Z'),
            'status' => $this->faker->randomElement(InvoiceStatus::cases())->value,
            'created_at' => $issuedAt->format('Y-m-d\TH:i:s.v\Z'),
            'updated_at' => $this->faker->dateTimeBetween($issuedAt, 'now')->format('Y-m-d\TH:i:s.v\Z'),
        ];
    }
    
    /**
     * Tạo invoice với trạng thái PAID
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InvoiceStatus::PAID->value,
        ]);
    }
    
    /**
     * Tạo invoice với trạng thái PENDING
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InvoiceStatus::PENDING->value,
        ]);
    }
    
    /**
     * Tạo invoice với order_id và user_id cụ thể
     */
    public function forOrderAndUser(string $orderId, string $userId): static
    {
        return $this->state(fn (array $attributes) => [
            'order_id' => $orderId,
            'user_id' => $userId,
        ]);
    }
    
    /**
     * Tạo invoice với số lượng items cụ thể
     */
    public function withItems(int $itemCount): static
    {
        return $this->state(function (array $attributes) use ($itemCount) {
            $items = [];
            $totalPrice = 0;
            
            for ($i = 0; $i < $itemCount; $i++) {
                $quantity = $this->faker->numberBetween(1, 3);
                $price = $this->faker->numberBetween(10000, 100000);
                $itemTotal = $quantity * $price;
                $totalPrice += $itemTotal;
                
                $items[] = [
                    'medicine_id' => $this->faker->uuid(),
                    'quantity' => $quantity,
                    'price' => $price,
                    'item_total' => $itemTotal
                ];
            }
            
            return [
                'items' => $items,
                'total_price' => $totalPrice,
            ];
        });
    }
}
