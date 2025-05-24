<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Cart;
use App\Enums\OrderStatus;
use App\Models\User;
use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\RouteAttributes\Attributes\Delete;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Patch;

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

    #[Get(uri: "/store/orders/{id}/details", name: "store.orders.getDetails")]
    /**
     * @OA\Get(
     *     path="/v1/store/orders/{id}/details",
     *     operationId="getOrderDetails",
     *     tags={"Orders"},
     *     summary="Lấy chi tiết đơn hàng",
     *     description="Trả về thông tin chi tiết của một đơn hàng cụ thể của người dùng đã đăng nhập",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của đơn hàng cần xem chi tiết",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="_id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="user_id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="status", type="string", example="pending"),
     *                 @OA\Property(property="items", type="array", 
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="medicine_id", type="string"),
     *                         @OA\Property(property="name", type="string"),
     *                         @OA\Property(property="quantity", type="integer"),
     *                         @OA\Property(property="price", type="number"),
     *                         @OA\Property(property="item_total", type="number"),
     *                         @OA\Property(property="medicine", type="object")
     *                     )
     *                 ),
     *                 @OA\Property(property="sub_total", type="number", example=30000),
     *                 @OA\Property(property="shipping_fee", type="number", example=15000),
     *                 @OA\Property(property="discount", type="number", example=0),
     *                 @OA\Property(property="total_price", type="number", example=45000),
     *                 @OA\Property(property="shipping_address", type="object"),
     *                 @OA\Property(property="payment_method", type="string", example="cod"),
     *                 @OA\Property(property="created_at", type="string", format="date-time")
     *             ),
     *             @OA\Property(property="message", type="string", example="Lấy chi tiết đơn hàng thành công"),
     *             @OA\Property(property="status", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy đơn hàng",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Đơn hàng không tồn tại"),
     *             @OA\Property(property="status", type="integer", example=404)
     *         )
     *     )
     * )
     */
    public function getOrderDetails($id)
    {
        $userId = Auth::id();
        $order = Order::where('user_id', $userId)->where('_id', $id)->first();
        if (!$order) return $this->fail(null, 'Đơn hàng không tồn tại', 404);
        $orderItems = $order->items;
        $medicineIds = array_column($orderItems, 'medicine_id');
        $medicines = Medicine::whereIn('_id', $medicineIds)->get()->keyBy('_id');
        $orderItems = collect($orderItems)->map(function ($item) use ($medicines) {
            $medicineId = $item['medicine_id'];
            $medicine = $medicines[$medicineId];
            $item['medicine'] = $medicine;
            return $item;
        })->toArray();
        $order->items = $orderItems;
        return $this->json($order, 'Lấy chi tiết đơn hàng thành công', 200);
    }

    #[Get(uri: "/admin/orders", name: "admin.orders.get", middleware: "role:admin")]
    /**
     * @OA\Get(
     *     path="/v1/admin/orders",
     *     operationId="getOrdersAdmin",
     *     tags={"Orders"},
     *     summary="Lấy danh sách tất cả đơn hàng cho admin",
     *     description="Trả về danh sách tất cả đơn hàng trong hệ thống (chỉ dành cho admin)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="_id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                         @OA\Property(property="user_id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                         @OA\Property(property="user", type="object",
     *                             @OA\Property(property="firstname", type="string", example="Nguyen"),
     *                             @OA\Property(property="lastname", type="string", example="Van A"),
     *                             @OA\Property(property="profile_image", type="string", example="https://example.com/image.jpg")
     *                         ),
     *                         @OA\Property(property="status", type="string", example="PENDING"),
     *                         @OA\Property(property="items", type="array",
     *                             @OA\Items(
     *                                 @OA\Property(property="medicine_id", type="string"),
     *                                 @OA\Property(property="name", type="string"),
     *                                 @OA\Property(property="quantity", type="integer"),
     *                                 @OA\Property(property="price", type="number"),
     *                                 @OA\Property(property="item_total", type="number"),
     *                                 @OA\Property(property="medicine", type="object")
     *                             )
     *                         ),
     *                         @OA\Property(property="sub_total", type="number", example=30000),
     *                         @OA\Property(property="shipping_fee", type="number", example=15000),
     *                         @OA\Property(property="discount", type="number", example=0),
     *                         @OA\Property(property="total_price", type="number", example=45000),
     *                         @OA\Property(property="shipping_address", type="object"),
     *                         @OA\Property(property="payment_method", type="string", example="cod"),
     *                         @OA\Property(property="created_at", type="string", format="date-time")
     *                     )
     *                 ),
     *                 @OA\Property(property="first_page_url", type="string", example="http://localhost/v1/admin/orders?page=1"),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(property="last_page_url", type="string", example="http://localhost/v1/admin/orders?page=5"),
     *                 @OA\Property(property="next_page_url", type="string", example="http://localhost/v1/admin/orders?page=2"),
     *                 @OA\Property(property="path", type="string", example="http://localhost/v1/admin/orders"),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="prev_page_url", type="string", example=null),
     *                 @OA\Property(property="to", type="integer", example=10),
     *                 @OA\Property(property="total", type="integer", example=50)
     *             ),
     *             @OA\Property(property="message", type="string", example="Lấy danh sách đơn hàng thành công"),
     *             @OA\Property(property="status", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Không có quyền truy cập",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Bạn không có quyền truy cập"),
     *             @OA\Property(property="status", type="integer", example=403)
     *         )
     *     )
     * )
     */
    public function getOrdersAdmin()
    {
        $orders = Order::paginate(10);
        $orders->map(function ($order) {
            $user = User::find($order->user_id);
            $order->user = $user ? [
                'email' => $user->email,
                'username' => $user->username,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'profile_image' => $user->profile_image,
            ] : null;
        });
        return $this->json($orders, 'Lấy danh sách đơn hàng thành công', 200);
    }

    #[Get(uri: "/admin/orders/{id}/details", name: "admin.orders.getById", middleware: "role:admin")]
    /**
     * @OA\Get(
     *     path="/v1/admin/orders/{id}/details",
     *     operationId="getOrderDetailsAdmin",
     *     tags={"Orders"},
     *     summary="Lấy chi tiết đơn hàng (Admin)",
     *     description="Trả về thông tin chi tiết của một đơn hàng cụ thể kèm thông tin người dùng",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của đơn hàng cần xem chi tiết",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="_id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="user_id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="status", type="string", example="PENDING"),
     *                 @OA\Property(property="items", type="array", 
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="medicine_id", type="string"),
     *                         @OA\Property(property="name", type="string"),
     *                         @OA\Property(property="quantity", type="integer"),
     *                         @OA\Property(property="price", type="number"),
     *                         @OA\Property(property="item_total", type="number"),
     *                         @OA\Property(property="medicine", type="object")
     *                     )
     *                 ),
     *                 @OA\Property(property="sub_total", type="number", example=30000),
     *                 @OA\Property(property="shipping_fee", type="number", example=15000),
     *                 @OA\Property(property="discount", type="number", example=0),
     *                 @OA\Property(property="total_price", type="number", example=45000),
     *                 @OA\Property(property="shipping_address", type="object"),
     *                 @OA\Property(property="payment_method", type="string", example="COD"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="email", type="string"),
     *                     @OA\Property(property="username", type="string"),
     *                     @OA\Property(property="firstname", type="string"),
     *                     @OA\Property(property="lastname", type="string"),
     *                     @OA\Property(property="profile_image", type="string")
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Lấy chi tiết đơn hàng thành công"),
     *             @OA\Property(property="status", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy đơn hàng",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Đơn hàng không tồn tại"),
     *             @OA\Property(property="status", type="integer", example=404)
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Không có quyền truy cập",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Bạn không có quyền truy cập"),
     *             @OA\Property(property="status", type="integer", example=403)
     *         )
     *     )
     * )
     */
    public function getOrderDetailsAdmin($id)
    {
        $order = Order::find($id);
        if (!$order) return $this->fail(null, 'Đơn hàng không tồn tại', 404);
        $user = User::find($order->user_id);
        $order->user = $user ? [
            'email' => $user->email,
            'username' => $user->username,
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'profile_image' => $user->profile_image,
        ] : null;
        $orderItems = $order->items;
        $medicineIds = array_column($orderItems, 'medicine_id');
        $medicines = Medicine::whereIn('_id', $medicineIds)->get()->keyBy('_id');
        $orderItems = collect($orderItems)->map(function ($item) use ($medicines) {
            $medicineId = $item['medicine_id'];
            $medicine = isset($medicines[$medicineId]) ? $medicines[$medicineId] : null;
            $item['medicine'] = $medicine;
            return $item;
        })->toArray();
        $order->setAttribute('items', $orderItems);
        return $this->json($order, 'Lấy chi tiết đơn hàng thành công', 200);
    }

    /**
     * @OA\Patch(
     *     path="/v1/admin/orders/{id}/status",
     *     operationId="updateOrderStatus",
     *     tags={"Orders"},
     *     summary="Cập nhật trạng thái đơn hàng",
     *     description="Cập nhật trạng thái của đơn hàng theo ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của đơn hàng",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="SHIPPED", 
     *                description="Trạng thái đơn hàng (PENDING, PROCESSING, SHIPPED, DELIVERED, CANCELLED, COMPLETED)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cập nhật trạng thái thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Cập nhật trạng thái đơn hàng thành công"),
     *             @OA\Property(property="status", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Đơn hàng không tồn tại",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Đơn hàng không tồn tại"),
     *             @OA\Property(property="status", type="integer", example=404)
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Không có quyền truy cập",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Bạn không có quyền truy cập"),
     *             @OA\Property(property="status", type="integer", example=403)
     *         )
     *     )
     * )
     */
    #[Patch(uri: "/admin/orders/{id}/status", name: "admin.orders.updateStatus", middleware: "role:admin")]
    public function updateOrderStatus($id, Request $request)
    {
        $order = Order::find($id);
        if (!$order) return $this->fail(null, 'Đơn hàng không tồn tại', 404);
        $order->status = $request->status;
        $order->save();
        return $this->json($order, 'Cập nhật trạng thái đơn hàng thành công', 200);
    }

    /**
     * @OA\Delete(
     *     path="/v1/admin/orders/{id}/delete",
     *     operationId="deleteOrder",
     *     tags={"Orders"},
     *     summary="Xóa đơn hàng",
     *     description="Xóa đơn hàng theo ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của đơn hàng cần xóa",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Xóa đơn hàng thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Xóa đơn hàng thành công"),
     *             @OA\Property(property="status", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Đơn hàng không tồn tại",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Đơn hàng không tồn tại"),
     *             @OA\Property(property="status", type="integer", example=404)
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Không có quyền truy cập",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Bạn không có quyền truy cập"),
     *             @OA\Property(property="status", type="integer", example=403)
     *         )
     *     )
     * )
     */
    #[Delete(uri: "/admin/orders/{id}/delete", name: "admin.orders.delete", middleware: "role:admin")]
    public function deleteOrder($id)
    {
        $order = Order::find($id);
        if (!$order) return $this->fail(null, 'Đơn hàng không tồn tại', 404);
        $order->delete();
        return $this->json(null, 'Xóa đơn hàng thành công', 200);
    }
}
