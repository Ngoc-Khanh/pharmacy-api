<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Cart;
use App\Enums\OrderStatus;
use App\Models\User;
use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Middleware;

#[Prefix(prefix: "v1")]
#[Middleware(middleware: "jwt.auth")]
/**
 * @OA\Tag(
 *     name="Orders",
 *     description="Quản lý đơn hàng"
 * )
 */
class OrderController extends Controller
{
    #[Get(uri: "/store/orders", name: "store.orders")]
    /**
     * @OA\Get(
     *     path="/v1/store/orders",
     *     operationId="getOrders",
     *     tags={"Orders"},
     *     summary="Lấy danh sách đơn hàng của người dùng",
     *     description="Trả về danh sách tất cả đơn hàng của người dùng đã đăng nhập",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", 
     *                @OA\Items(
     *                    @OA\Property(property="_id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                    @OA\Property(property="user_id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                    @OA\Property(property="status", type="string", example="pending"),
     *                    @OA\Property(property="items", type="array", @OA\Items(type="object")),
     *                    @OA\Property(property="sub_total", type="number", example=30000),
     *                    @OA\Property(property="shipping_fee", type="number", example=15000),
     *                    @OA\Property(property="discount", type="number", example=0),
     *                    @OA\Property(property="total_price", type="number", example=45000),
     *                    @OA\Property(property="shipping_address", type="object"),
     *                    @OA\Property(property="payment_method", type="string", example="cod"),
     *                    @OA\Property(property="created_at", type="string", format="date-time", example="2023-06-15T14:30:00Z")
     *                )
     *             ),
     *             @OA\Property(property="message", type="string", example="Lấy danh sách đơn hàng thành công"),
     *             @OA\Property(property="status", type="integer", example=200)
     *         )
     *     )
     * )
     */
    public function getOrders()
    {
        $userId = Auth::id();
        $orders = Order::where('user_id', $userId)->get();
        return $this->json($orders, 'Lấy danh sách đơn hàng thành công', 200);
    }

    #[Post(uri: "/store/orders/add", name: "store.orders.add")]
    /**
     * @OA\Post(
     *     path="/v1/store/orders/add",
     *     operationId="addOrders",
     *     tags={"Orders"},
     *     summary="Tạo đơn hàng mới",
     *     description="Tạo đơn hàng mới từ giỏ hàng của người dùng đã đăng nhập",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"shipping_address_id", "payment_method"},
     *             @OA\Property(property="shipping_address_id", type="string", example="550e8400-e29b-41d4-a716-446655440000", description="ID của địa chỉ giao hàng"),
     *             @OA\Property(property="payment_method", type="string", example="COD", description="Phương thức thanh toán", enum={"COD", "CREDIT-CARD", "BANK-TRANSFER"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Đặt hàng thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="_id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="user_id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="status", type="string", example="pending"),
     *                 @OA\Property(property="items", type="array", 
     *                     @OA\Items(
     *                         @OA\Property(property="medicine_id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                         @OA\Property(property="quantity", type="integer", example=2),
     *                         @OA\Property(property="price", type="number", example=15000),
     *                         @OA\Property(property="item_total", type="number", example=30000),
     *                         @OA\Property(property="medicine", type="object",
     *                             @OA\Property(property="name", type="string", example="Paracetamol"),
     *                             @OA\Property(property="thumbnail", type="object",
     *                                 @OA\Property(property="url", type="string", example="https://example.com/images/paracetamol.jpg"),
     *                                 @OA\Property(property="alt", type="string", example="paracetamol-alt")
     *                             )
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(property="sub_total", type="number", example=30000),
     *                 @OA\Property(property="shipping_fee", type="number", example=15000),
     *                 @OA\Property(property="discount", type="number", example=0),
     *                 @OA\Property(property="total_price", type="number", example=45000),
     *                 @OA\Property(property="shipping_address", type="object",
     *                     @OA\Property(property="id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                     @OA\Property(property="name", type="string", example="Nguyễn Văn A"),
     *                     @OA\Property(property="phone", type="string", example="0901234567"),
     *                     @OA\Property(property="address_line1", type="string", example="123 Đường Nguyễn Huệ"),
     *                     @OA\Property(property="city", type="string", example="Quận 1"),
     *                     @OA\Property(property="state", type="string", example="TP Hồ Chí Minh"),
     *                     @OA\Property(property="country", type="string", example="Việt Nam")
     *                 ),
     *                 @OA\Property(property="payment_method", type="string", example="COD"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2023-06-15T14:30:00Z")
     *             ),
     *             @OA\Property(property="message", type="string", example="Đặt hàng thành công"),
     *             @OA\Property(property="status", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Lỗi dữ liệu",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Giỏ hàng trống"),
     *             @OA\Property(property="status", type="integer", example=400)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Dữ liệu không hợp lệ",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="The shipping address id field is required."),
     *             @OA\Property(property="status", type="integer", example=422),
     *             @OA\Property(property="error", type="object")
     *         )
     *     )
     * )
     */
    public function addOrders(Request $request)
    {
        $request->validate([
            'shipping_address_id' => 'required|string',
            'payment_method' => 'required|in:COD,CREDIT-CARD,BANK-TRANSFER',
        ]);
        $userId = Auth::id();
        $user = User::find($userId);
        $cart = Cart::where('user_id', $userId)->first();
        if (!$cart || empty($cart->items)) return $this->fail(null, 'Giỏ hàng trống', 400);
        $selectedAddress = null;
        foreach ($user->addresses as $address) {
            if ($address['id'] == $request->shipping_address_id) {
                $selectedAddress = $address;
                break;
            }
        }
        if (!$selectedAddress) return $this->fail(null, 'Địa chỉ không tồn tại', 400);
        $orderItems = [];
        $subTotal = 0;
        $shippingFee = 15000; // Default shipping fee
        $discount = 0;
        $medicineIds = array_column($cart->items, 'medicine_id');
        $medicines = Medicine::whereIn('_id', $medicineIds)->get()->keyBy('_id');
        foreach ($cart->items as $item) {
            $medicineId = $item['medicine_id'];
            if (!isset($medicines[$medicineId])) return $this->fail(null, 'Sản phẩm không tồn tại: ' . ($item['name'] ?? 'Unknown'), 400);
            $medicine = $medicines[$medicineId];
            $currentPrice = $medicine->variants['price'];
            $quantity = (int)$item['quantity'];
            $orderItem = $item;
            $orderItem['price'] = $currentPrice;
            $orderItem['item_total'] = $currentPrice * $quantity;
            $orderItems[] = $orderItem;
            $subTotal += $currentPrice * $quantity;
        }
        if ($subTotal > 300000) $discount = $subTotal * 0.05; // 5% discount for orders above 300,000 VND
        if ($subTotal > 500000) $shippingFee = 0;
        $totalPrice = $subTotal + $shippingFee - $discount;
        $order = Order::create([
            'user_id' => $userId,
            'status' => OrderStatus::PENDING->value,
            'items' => $orderItems,
            'sub_total' => $subTotal,
            'shipping_fee' => $shippingFee,
            'discount' => $discount,
            'total_price' => $totalPrice,
            'shipping_address' => $selectedAddress,
            'payment_method' => $request->payment_method,
            'created_at' => now(),
        ]);
        $cart->update(['items' => []]);
        return $this->json($order, 'Đặt hàng thành công', 200);
    }
}
