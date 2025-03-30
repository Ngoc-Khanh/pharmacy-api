<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Str;
use MongoDB\BSON\ObjectId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Middleware;

#[Prefix('v2/order')]
#[Middleware('jwt.auth')]
class OrderController extends Controller
{
    #[Get('/my-orders', 'order.myOrders')]
    public function myOrders()
    {
        $user = Auth::user();
        $orders = Order::where('user_id', $user->_id)->get()->makeHidden(['user_id']);
        return $this->json($orders, "Orders retrieved successfully", 200);
    }
    #[Post("/create-order", "order.create")]
    public function createOrder(Request $request)
    {
        $user = Auth::user();
        if (!$user) return $this->fail([], "Unauthorized", 401);
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.medicine_id' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'shipping_address' => 'required|string',
            'payment_method' => 'required|string',
            'note' => 'nullable|string',
        ]);
        $itemsInput = $request->input('items');
        $orderItems = [];
        $subtotal = 0;
        // Duyệt từng item để tính giá và cấu trúc lại dữ liệu cho order
        foreach ($itemsInput as $item) {
            $medicine = Medicine::find(new ObjectId($item['medicine_id']));
            if (!$medicine) return $this->fail([], "Medicine not found" . $item['medicine_id'], 404);
            $price = $medicine->variants['price'] ?? 0;
            $discount = $medicine->variants['is_discount'] ? $medicine->variants['discount_percent'] : 0;
            $quantity = (int)$item['quantity'];
            $priceAfterDiscount = $price - (($price * $discount) / 100);
            $totalItem = $priceAfterDiscount * $quantity;
            $subtotal += $totalItem;
            $orderItems[] = [
                'medicine_id' => $medicine->_id,
                'name' => $medicine->name,
                'quantity' => $quantity,
                'price' => $price,
                'discount_percent' => $discount,
                'total_item' => $totalItem,
            ];
        }
        $shippingFee = 5000;
        $total = $subtotal + $shippingFee;
        $orderNumber = 'ORD' . Carbon::now()->format('Ymd') . strtoupper(Str::random(4));
        $order = Order::create([
            'user_id' => $user->_id,
            'order_number' => $orderNumber,
            'status' => 'pending',
            'items' => $orderItems,
            'subtotal' => $subtotal,
            'shipping_fee' => $shippingFee,
            'total' => $total,
            'payment_method' => $request->input('payment_method'),
            'shipping_address' => $request->input('shipping_address'),
            'note' => $request->input('note'),
        ]);
        return $this->json($order, "Order created successfully", 201);
    }
}
