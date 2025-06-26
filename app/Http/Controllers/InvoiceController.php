<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Enums\OrderStatus;
use App\Models\Invoice;
use App\Models\Medicine;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;
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
 *     name="Invoices",
 *     description="Quản lý hóa đơn"
 * )
 */
class InvoiceController extends Controller
{
    #[Get(uri: "/store/invoices/list", name: "store.invoices.list")]
    /**
     * @OA\Get(
     *     path="/v1/store/invoices/list",
     *     operationId="getUserInvoices",
     *     tags={"Invoices"},
     *     summary="Lấy danh sách hóa đơn của người dùng",
     *     description="Trả về danh sách tất cả hóa đơn của người dùng đã đăng nhập",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", 
     *                @OA\Items(
     *                    @OA\Property(property="_id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                    @OA\Property(property="order_id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                    @OA\Property(property="user_id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                    @OA\Property(property="invoice_number", type="string", example="INV-20230615-001"),
     *                    @OA\Property(property="items", type="array", @OA\Items(type="object")),
     *                    @OA\Property(property="total_price", type="number", example=45000),
     *                    @OA\Property(property="payment_method", type="string", example="COD"),
     *                    @OA\Property(property="status", type="string", example="PAID"),
     *                    @OA\Property(property="issued_at", type="string", format="date-time"),
     *                    @OA\Property(property="created_at", type="string", format="date-time")
     *                )
     *             ),
     *             @OA\Property(property="message", type="string", example="Lấy danh sách hóa đơn thành công"),
     *             @OA\Property(property="status", type="integer", example=200)
     *         )
     *     )
     * )
     */
    public function getInvoices()
    {
        $userId = Auth::id();
        $invoices = Invoice::where('user_id', $userId)->get();
        return $this->json($invoices, 'Lấy danh sách hóa đơn thành công', 200);
    }

    #[Get(uri: "/store/invoices/{id}/details", name: "store.invoices.details")]
    /**
     * @OA\Get(
     *     path="/v1/store/invoices/{id}/details",
     *     operationId="getInvoiceDetails",
     *     tags={"Invoices"},
     *     summary="Lấy chi tiết hóa đơn",
     *     description="Trả về thông tin chi tiết của một hóa đơn theo ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của hóa đơn",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lấy chi tiết hóa đơn thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="_id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="invoice_number", type="string", example="INV-20230615-001"),
     *                 @OA\Property(property="order", type="object",
     *                     @OA\Property(property="shipping_fee", type="number", example=15000),
     *                     @OA\Property(property="discount", type="number", example=5000),
     *                     @OA\Property(property="shipping_address", type="object",
     *                         @OA\Property(property="name", type="string", example="Nguyễn Văn A"),
     *                         @OA\Property(property="phone", type="string", example="0123456789"),
     *                         @OA\Property(property="address", type="string", example="123 Đường ABC, Phường XYZ")
     *                     )
     *                 ),
     *                 @OA\Property(property="items", type="array", 
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="medicine_id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                         @OA\Property(property="quantity", type="integer", example=2),
     *                         @OA\Property(property="price", type="number", example=15000),
     *                         @OA\Property(property="medicine", type="object",
     *                             @OA\Property(property="_id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                             @OA\Property(property="name", type="string", example="Paracetamol"),
     *                             @OA\Property(property="thumbnail", type="object",
     *                                 @OA\Property(property="url", type="string", example="https://example.com/images/paracetamol.jpg")
     *                             )
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(property="total_price", type="number", example=45000),
     *                 @OA\Property(property="payment_method", type="string", example="COD"),
     *                 @OA\Property(property="status", type="string", example="PAID", description="PENDING, PAID, CANCELLED, REFUNDED")
     *             ),
     *             @OA\Property(property="message", type="string", example="Lấy chi tiết hóa đơn thành công"),
     *             @OA\Property(property="status", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Hóa đơn không tồn tại",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Hóa đơn không tồn tại"),
     *             @OA\Property(property="status", type="integer", example=400)
     *         )
     *     )
     * )
     */
    public function invoicesDetails($id)
    {
        $userId = Auth::id();
        $invoice = Invoice::where('user_id', $userId)->where('_id', $id)->first();
        if (!$invoice) return $this->fail(null, 'Hóa đơn không tồn tại', 400);
        $order = Order::where('_id', $invoice->order_id)->first();
        if (!$order) return $this->fail(null, 'Đơn hàng không tồn tại', 400);
        $invoice->order = [
            'shipping_fee' => $order->shipping_fee,
            'discount' => $order->discount,
            'shipping_address' => $order->shipping_address,
        ];
        $orderItems = $order->items;
        $medicineIds = array_column($orderItems, 'medicine_id');
        $medicines = Medicine::whereIn('_id', $medicineIds)->get()->keyBy('_id');
        $orderItems = collect($orderItems)->map(function ($item) use ($medicines) {
            if (isset($medicines[$item['medicine_id']])) {
                $item['medicine'] = $medicines[$item['medicine_id']];
            }
            return $item;
        });
        $invoice->items = $orderItems;
        return $this->json($invoice, 'Lấy chi tiết hóa đơn thành công', 200);
    }

    #[Get(uri: "/store/invoices/{id}/details-with-orders-id", name: "store.invoices.detailsWithOrdersId")]
    /**
     * @OA\Get(
     *     path="/v1/store/invoices/{id}/details-with-orders-id",
     *     operationId="getInvoiceDetailsByOrderId",
     *     tags={"Invoices"},
     *     summary="Lấy chi tiết hóa đơn theo order ID",
     *     description="Lấy chi tiết hóa đơn bằng cách sử dụng order ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của đơn hàng",
     *         @OA\Schema(type="string", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lấy chi tiết hóa đơn thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="_id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="order_id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="user_id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="invoice_number", type="string", example="INV-20230615-001"),
     *                 @OA\Property(property="order", type="object",
     *                     @OA\Property(property="shipping_fee", type="number", example=15000),
     *                     @OA\Property(property="discount", type="number", example=5000),
     *                     @OA\Property(property="shipping_address", type="string", example="123 Nguyễn Trãi, Q.1, TP.HCM")
     *                 ),
     *                 @OA\Property(property="items", type="array", 
     *                     @OA\Items(
     *                         @OA\Property(property="medicine_id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                         @OA\Property(property="quantity", type="integer", example=2),
     *                         @OA\Property(property="price", type="number", example=15000),
     *                         @OA\Property(property="item_total", type="number", example=30000),
     *                         @OA\Property(property="medicine", type="object",
     *                             @OA\Property(property="_id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                             @OA\Property(property="name", type="string", example="Paracetamol 500mg"),
     *                             @OA\Property(property="price", type="number", example=15000),
     *                             @OA\Property(property="thumbnail", type="object",
     *                                 @OA\Property(property="url", type="string", example="https://example.com/images/paracetamol.jpg")
     *                             )
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(property="total_price", type="number", example=45000),
     *                 @OA\Property(property="payment_method", type="string", example="COD"),
     *                 @OA\Property(property="status", type="string", example="PAID", description="PENDING, PAID, CANCELLED, REFUNDED")
     *             ),
     *             @OA\Property(property="message", type="string", example="Lấy chi tiết hóa đơn thành công"),
     *             @OA\Property(property="status", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Đơn hàng hoặc hóa đơn không tồn tại",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Đơn hàng không tồn tại"),
     *             @OA\Property(property="status", type="integer", example=400)
     *         )
     *     )
     * )
     */
    public function invoicesDetailsWithOrdersId($id)
    {
        $order = Order::where('_id', $id)->first();
        if (!$order) return $this->fail(null, 'Đơn hàng không tồn tại', 400);
        $invoice = Invoice::where('order_id', $order->_id)->first();
        if (!$invoice) return $this->fail(null, 'Hóa đơn không tồn tại', 400);
        $invoice->order = [
            'shipping_fee' => $order->shipping_fee,
            'discount' => $order->discount,
            'shipping_address' => $order->shipping_address,
        ];
        $orderItems = $order->items;
        $medicineIds = array_column($orderItems, 'medicine_id');
        $medicines = Medicine::whereIn('_id', $medicineIds)->get()->keyBy('_id');
        $orderItems = collect($orderItems)->map(function ($item) use ($medicines) {
            if (isset($medicines[$item['medicine_id']])) {
                $item['medicine'] = $medicines[$item['medicine_id']];
            }
            return $item;
        });
        $invoice->items = $orderItems;
        return $this->json($invoice, 'Lấy chi tiết hóa đơn thành công', 200);
    }

    #[Post(uri: "/store/invoices/create", name: "store.invoices.create")]
    /**
     * @OA\Post(
     *     path="/v1/store/invoices/create",
     *     operationId="createInvoice",
     *     tags={"Invoices"},
     *     summary="Tạo hóa đơn mới",
     *     description="Tạo hóa đơn mới từ một đơn hàng hiện có",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"order_id"},
     *             @OA\Property(property="order_id", type="string", example="550e8400-e29b-41d4-a716-446655440000", description="ID của đơn hàng"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tạo hóa đơn thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="_id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="order_id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="user_id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="invoice_number", type="string", example="INV-20230615-001"),
     *                 @OA\Property(property="items", type="array", 
     *                     @OA\Items(
     *                         @OA\Property(property="medicine_id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                         @OA\Property(property="quantity", type="integer", example=2),
     *                         @OA\Property(property="price", type="number", example=15000),
     *                         @OA\Property(property="item_total", type="number", example=30000)
     *                     )
     *                 ),
     *                 @OA\Property(property="total_price", type="number", example=45000),
     *                 @OA\Property(property="payment_method", type="string", example="cod"),
     *                 @OA\Property(property="issued_at", type="string", format="date-time", example="2023-06-15T14:30:00Z"),
     *                 @OA\Property(property="status", type="string", example="paid"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2023-06-15T14:30:00Z")
     *             ),
     *             @OA\Property(property="message", type="string", example="Tạo hóa đơn thành công"),
     *             @OA\Property(property="status", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Lỗi dữ liệu",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Đơn hàng không tồn tại"),
     *             @OA\Property(property="status", type="integer", example=400)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Dữ liệu không hợp lệ",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="The order id field is required."),
     *             @OA\Property(property="status", type="integer", example=422),
     *             @OA\Property(property="error", type="object")
     *         )
     *     )
     * )
     */
    public function createInvoice(Request $request)
    {
        $request->validate([
            'order_id' => 'required|string',
        ]);
        $userId = Auth::id();
        $order = Order::where('_id', $request->order_id)
            ->where('user_id', $userId)
            ->first();
        if (!$order) return $this->fail(null, 'Đơn hàng không tồn tại', 400);
        $existingInvoice = Invoice::where('order_id', $order->_id)->first();
        if ($existingInvoice) return $this->fail(null, 'Hóa đơn cho đơn hàng này đã tồn tại', 400);
        $today = date('Ymd');
        $lastInvoice = Invoice::where('invoice_number', 'like', "INV-{$today}-%")
            ->orderBy('invoice_number', 'desc')
            ->first();
        $sequenceNumber = 1;
        if ($lastInvoice) {
            $parts = explode('-', $lastInvoice->invoice_number);
            $sequenceNumber = (int)end($parts) + 1;
        }
        $invoiceNumber = sprintf("INV-%s-%03d", $today, $sequenceNumber);
        $invoice = Invoice::create([
            'order_id' => $order->_id,
            'user_id' => $userId,
            'invoice_number' => $invoiceNumber,
            'items' => $order->items,
            'total_price' => $order->total_price,
            'payment_method' => $order->payment_method,
            'issued_at' => now(),
            'status' => InvoiceStatus::PENDING,
            'created_at' => now(),
        ]);
        return $this->json($invoice, 'Tạo hóa đơn thành công', 200);
    }

    #[Get(uri: "/admin/invoices/list", name: "admin.invoices.list", middleware: "role:admin")]
    /**
     * @OA\Get(
     *     path="/v1/admin/invoices/list",
     *     operationId="adminInvoicesList",
     *     tags={"Invoices"},
     *     summary="Lấy danh sách tất cả hóa đơn",
     *     description="Trả về danh sách tất cả hóa đơn trong hệ thống với khả năng tìm kiếm và phân trang (chỉ dành cho admin)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="s",
     *         in="query",
     *         description="Từ khóa tìm kiếm (tìm theo invoice_number, status)",
     *         required=false,
     *         @OA\Schema(type="string", example="INV-20240310")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Số lượng bản ghi trên mỗi trang",
     *         required=false,
     *         @OA\Schema(type="integer", example=10, default=10)
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Trường để sắp xếp",
     *         required=false,
     *         @OA\Schema(type="string", enum={"invoice_number", "status", "created_at", "updated_at"}, example="created_at")
     *     ),
     *     @OA\Parameter(
     *         name="sort_order",
     *         in="query",
     *         description="Thứ tự sắp xếp",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"}, example="desc")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lấy danh sách hóa đơn thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array", 
     *                     @OA\Items(
     *                         @OA\Property(property="_id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                         @OA\Property(property="order_id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                         @OA\Property(property="user_id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                         @OA\Property(property="invoice_number", type="string", example="INV-20240310-001"),
     *                         @OA\Property(property="items", type="array", @OA\Items(type="object")),
     *                         @OA\Property(property="total_price", type="number", example=150000),
     *                         @OA\Property(property="payment_method", type="string", example="bank_transfer"),
     *                         @OA\Property(property="status", type="string", example="PENDING", description="PENDING, PAID, CANCELLED, REFUNDED"),
     *                         @OA\Property(property="issued_at", type="string", format="date-time"),
     *                         @OA\Property(property="created_at", type="string", format="date-time"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time")
     *                     )
     *                 ),
     *                 @OA\Property(property="first_page_url", type="string", example="http://localhost/v1/admin/invoices/list?page=1"),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=10),
     *                 @OA\Property(property="last_page_url", type="string", example="http://localhost/v1/admin/invoices/list?page=10"),
     *                 @OA\Property(property="links", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="next_page_url", type="string", example="http://localhost/v1/admin/invoices/list?page=2"),
     *                 @OA\Property(property="path", type="string", example="http://localhost/v1/admin/invoices/list"),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="prev_page_url", type="string", nullable=true),
     *                 @OA\Property(property="to", type="integer", example=10),
     *                 @OA\Property(property="total", type="integer", example=100)
     *             ),
     *             @OA\Property(property="message", type="string", example="Lấy danh sách hóa đơn thành công"),
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="locale", type="string", example="vi_VN"),
     *             @OA\Property(property="error", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Không được phép truy cập",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Không được phép truy cập"),
     *             @OA\Property(property="status", type="integer", example=401),
     *             @OA\Property(property="locale", type="string", example="vi_VN"),
     *             @OA\Property(property="error", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Không đủ quyền truy cập",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Bạn không có quyền truy cập tính năng này"),
     *             @OA\Property(property="status", type="integer", example=403),
     *             @OA\Property(property="locale", type="string", example="vi_VN"),
     *             @OA\Property(property="error", type="object")
     *         )
     *     )
     * )
     */
    public function invoiceAdminList(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $sortField = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $search = $request->input('s', '');
        $allowedSortFields = ['invoice_number', 'status', 'created_at', 'updated_at'];
        if (!in_array($sortField, $allowedSortFields)) $sortField = 'created_at';
        $query = Invoice::query();
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            });
        }
        $invoices = $query->orderBy($sortField, $sortOrder === 'asc' ? 'asc' : 'desc')->paginate($perPage);
        return $this->json($invoices, 'Lấy danh sách hóa đơn thành công', 200);
    }

    #[Post(uri: "/admin/invoices/create-with-no-order", name: "admin.invoices.create-with-no-order", middleware: "role:admin")]
    /**
     * @OA\Post(
     *     path="/v1/admin/invoices/create-with-no-order",
     *     operationId="adminCreateInvoiceWithNoOrder",
     *     tags={"Invoices"},
     *     summary="Tạo hóa đơn và tự động sinh đơn hàng",
     *     description="Tạo một hóa đơn mới và tự động tạo đơn hàng tương ứng (chỉ dành cho admin)",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "items", "payment_method", "shipping_address"},
     *             @OA\Property(property="user_id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *             @OA\Property(property="invoice_number", type="string", example="INV-20240310-001", description="Tùy chọn, sẽ tự động tạo nếu không cung cấp"),
     *             @OA\Property(property="items", type="array", 
     *                 @OA\Items(
     *                     @OA\Property(property="medicine_id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                     @OA\Property(property="quantity", type="integer", example=2),
     *                     @OA\Property(property="price", type="number", example=15000)
     *                 )
     *             ),
     *             @OA\Property(property="payment_method", type="string", example="COD", enum={"COD", "CREDIT-CARD", "BANK-TRANSFER"}),
     *             @OA\Property(property="shipping_address", type="object",
     *                 @OA\Property(property="name", type="string", example="Nguyễn Văn A"),
     *                 @OA\Property(property="phone", type="string", example="0901234567"),
     *                 @OA\Property(property="address_line1", type="string", example="123 Đường Nguyễn Huệ"),
     *                 @OA\Property(property="city", type="string", example="Quận 1"),
     *                 @OA\Property(property="state", type="string", example="TP Hồ Chí Minh"),
     *                 @OA\Property(property="country", type="string", example="Việt Nam")
     *             ),
     *             @OA\Property(property="issued_at", type="string", format="date-time", description="Tùy chọn, mặc định là thời gian hiện tại"),
     *             @OA\Property(property="status", type="string", example="PENDING", description="Tùy chọn, mặc định là PENDING")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tạo hóa đơn và đơn hàng thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="order", type="object",
     *                     @OA\Property(property="_id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                     @OA\Property(property="user_id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                     @OA\Property(property="status", type="string", example="PENDING"),
     *                     @OA\Property(property="items", type="array", @OA\Items(type="object")),
     *                     @OA\Property(property="sub_total", type="number", example=30000),
     *                     @OA\Property(property="shipping_fee", type="number", example=15000),
     *                     @OA\Property(property="discount", type="number", example=0),
     *                     @OA\Property(property="total_price", type="number", example=45000),
     *                     @OA\Property(property="shipping_address", type="object"),
     *                     @OA\Property(property="payment_method", type="string", example="COD"),
     *                     @OA\Property(property="created_at", type="string", format="date-time")
     *                 ),
     *                 @OA\Property(property="invoice", type="object",
     *                     @OA\Property(property="_id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                     @OA\Property(property="order_id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                     @OA\Property(property="user_id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                     @OA\Property(property="invoice_number", type="string", example="INV-20240310-001"),
     *                     @OA\Property(property="items", type="array", @OA\Items(type="object")),
     *                     @OA\Property(property="total_price", type="number", example=45000),
     *                     @OA\Property(property="payment_method", type="string", example="COD"),
     *                     @OA\Property(property="issued_at", type="string", format="date-time"),
     *                     @OA\Property(property="status", type="string", example="PENDING"),
     *                     @OA\Property(property="created_at", type="string", format="date-time")
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Tạo hóa đơn và đơn hàng thành công"),
     *             @OA\Property(property="status", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Lỗi dữ liệu",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Người dùng không tồn tại"),
     *             @OA\Property(property="status", type="integer", example=400)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Dữ liệu không hợp lệ",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="The user id field is required."),
     *             @OA\Property(property="status", type="integer", example=422),
     *             @OA\Property(property="error", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Không có quyền truy cập",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Unauthorized"),
     *             @OA\Property(property="status", type="integer", example=401)
     *         )
     *     )
     * )
     */
    public function createInvoiceWithNoOrder(Request $request)
    {
        $request->validate([
            'user_id' => 'required|string',
            'order_id' => 'nullable|string',
            'invoice_number' => 'nullable|string|unique:invoices,invoice_number',
            'items' => 'required|array',
            'items.*.medicine_id' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'payment_method' => 'required|string|in:COD,CREDIT-CARD,BANK-TRANSFER',
            'shipping_address' => 'required|array',
            'shipping_address.name' => 'required|string',
            'shipping_address.phone' => 'required|string',
            'shipping_address.address_line1' => 'required|string',
            'shipping_address.city' => 'required|string',
            'shipping_address.state' => 'required|string',
            'shipping_address.country' => 'required|string',
            'issued_at' => 'nullable|date',
            'status' => 'nullable|string',
        ]);
        $data = $request->all();
        // Kiểm tra user tồn tại
        $user = User::find($data['user_id']);
        if (!$user) return $this->fail(null, 'Người dùng không tồn tại', 400);
        $medicineIds = array_column($data['items'], 'medicine_id');
        $medicines = Medicine::whereIn('_id', $medicineIds)->get()->keyBy('_id');
        foreach ($data['items'] as $item) {
            if (!isset($medicines[$item['medicine_id']])) return $this->fail(null, 'Sản phẩm không tồn tại: ' . $item['medicine_id'], 400);
        }
        // Tự động tạo invoice_number nếu không có
        if (!isset($data['invoice_number']) || !$data['invoice_number']) {
            $today = date('Ymd');
            $lastInvoice = Invoice::where('invoice_number', 'like', "INV-{$today}%")
                ->orderBy('invoice_number', 'desc')
                ->first();
            $sequenceNumber = 1;
            if ($lastInvoice) {
                $parts = explode('-', $lastInvoice->invoice_number);
                $sequenceNumber = (int)end($parts) + 1;
            }
            $data['invoice_number'] = sprintf("INV-%s-%03d", $today, $sequenceNumber);
        }
        // Tính toán giá từng item và tổng giá (tham khảo logic từ OrderController)
        $orderItems = [];
        $subTotal = 0;
        $shippingFee = 15000; // Default shipping fee
        $discount = 0;
        foreach ($data['items'] as $item) {
            $medicine = $medicines[$item['medicine_id']];
            $itemTotal = $item['quantity'] * $item['price'];
            $orderItem = [
                'medicine_id' => $item['medicine_id'],
                'name' => $medicine->name,
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'item_total' => $itemTotal,
            ];
            $orderItems[] = $orderItem;
            $subTotal += $itemTotal;
        }
        if ($subTotal > 300000) $discount = $subTotal * 0.05; // 5% discount for orders above 300,000 VND
        if ($subTotal > 500000) $shippingFee = 0; // Free shipping for orders above 500,000 VND
        $totalPrice = $subTotal + $shippingFee - $discount;
        // Tạo đơn hàng trước
        $order = Order::create([
            'user_id' => $data['user_id'],
            'status' => OrderStatus::PENDING->value,
            'items' => $orderItems,
            'sub_total' => $subTotal,
            'shipping_fee' => $shippingFee,
            'discount' => $discount,
            'total_price' => $totalPrice,
            'shipping_address' => $data['shipping_address'],
            'payment_method' => $data['payment_method'],
            'created_at' => now(),
        ]);

        // Tạo hóa đơn với thông tin đơn hàng
        $invoiceData = [
            'order_id' => $order->_id,
            'user_id' => $data['user_id'],
            'invoice_number' => $data['invoice_number'],
            'items' => $orderItems,
            'total_price' => $totalPrice,
            'payment_method' => $data['payment_method'],
            'issued_at' => $data['issued_at'] ?? now(),
            'status' => $data['status'] ?? InvoiceStatus::PENDING,
            'created_at' => now(),
        ];
        $invoice = Invoice::create($invoiceData);
        // Trả về kết quả với cả order và invoice
        $result = [
            'order' => $order,
            'invoice' => $invoice,
        ];
        return $this->json($result, 'Tạo hóa đơn và đơn hàng thành công', 200);
    }

    #[Get(uri: "/admin/invoices/{id}/detail", name: "admin.invoices.details", middleware: "role:admin")]
    public function getAdminInvoiceDetails($id)
    {
        $invoice = Invoice::where('_id', $id)->first();
        if (!$invoice) return $this->fail(null, 'Hóa đơn không tồn tại', 400);
        $user = User::find($invoice->user_id);
        if ($user) {
            $invoice->user = [
                'id' => $user->_id,
                'username' => $user->username,
                'email' => $user->email,
                'phone' => $user->phone,
                'profile_image' => $user->profile_image,
            ];
        }
        $order = Order::where('_id', $invoice->order_id)->first();
        if ($order) {
            $invoice->order = [
                'shipping_fee' => $order->shipping_fee,
                'discount' => $order->discount,
                'shipping_address' => $order->shipping_address,
            ];
            $invoice->items = collect($order->items)->map(function ($item) {
                $medicine = Medicine::find($item['medicine_id']);
                if ($medicine) {
                    $item['medicine'] = $medicine;
                }
                return $item;
            });
        }
        return $this->json($invoice, 'Lấy chi tiết hóa đơn thành công', 200);
    }

    #[Patch(uri: "/admin/invoices/{id}/change-status", name: "admin.invoices.change-status", middleware: "role:admin")]
    /**
     * @OA\Patch(
     *     path="/v1/admin/invoices/{id}/change-status",
     *     operationId="adminChangeInvoiceStatus",
     *     tags={"Invoices"},
     *     summary="Thay đổi trạng thái hóa đơn",
     *     description="Thay đổi trạng thái của hóa đơn (chỉ dành cho admin)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của hóa đơn cần thay đổi trạng thái",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(
     *                 property="status", 
     *                 type="string", 
     *                 enum={"PENDING", "PAID", "CANCELLED", "REFUNDED"},
     *                 example="PAID",
     *                 description="Trạng thái mới của hóa đơn"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thay đổi trạng thái hóa đơn thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="_id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="invoice_number", type="string", example="INV-20240310-001"),
     *                 @OA\Property(property="status", type="string", example="PAID"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             ),
     *             @OA\Property(property="message", type="string", example="Thay đổi trạng thái hóa đơn thành công"),
     *             @OA\Property(property="status", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Hóa đơn không tồn tại",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Hóa đơn không tồn tại"),
     *             @OA\Property(property="status", type="integer", example=400)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Dữ liệu không hợp lệ",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="The status field is required."),
     *             @OA\Property(property="status", type="integer", example=422),
     *             @OA\Property(property="error", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Không có quyền truy cập",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Unauthorized"),
     *             @OA\Property(property="status", type="integer", example=401)
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Không đủ quyền truy cập",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Bạn không có quyền truy cập vào tài nguyên này"),
     *             @OA\Property(property="status", type="integer", example=403)
     *         )
     *     )
     * )
     */
    public function changeInvoiceStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => ['required', new Enum(InvoiceStatus::class)]
        ]);
        if ($validator->fails()) return $this->fail(null, $validator->errors()->first(), 422);
        $invoice = Invoice::where("_id", $id)->first();
        if (!$invoice) return $this->fail(null, 'Hóa đơn không tồn tại', 400);
        $invoice->status = $request->status;
        $invoice->save();
        return $this->json($invoice, 'Thay đổi trạng thái hóa đơn thành công', 200);
    }

    #[Delete(uri: "/admin/invoices/{id}/delete", name: "admin.invoices.delete", middleware: "role:admin")]
    /**
     * @OA\Delete(
     *     path="/v1/admin/invoices/{id}/delete",
     *     operationId="adminDeleteInvoice",
     *     tags={"Invoices"},
     *     summary="Xóa hóa đơn",
     *     description="Xóa một hóa đơn khỏi hệ thống (chỉ dành cho admin)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của hóa đơn cần xóa",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Xóa hóa đơn thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Xóa hóa đơn thành công"),
     *             @OA\Property(property="status", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Hóa đơn không tồn tại",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Hóa đơn không tồn tại"),
     *             @OA\Property(property="status", type="integer", example=400)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Không có quyền truy cập",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Unauthorized"),
     *             @OA\Property(property="status", type="integer", example=401)
     *         )
     *     )
     * )
     */
    public function deleteInvoice($id)
    {
        $invoice = Invoice::where('_id', $id)->first();
        if (!$invoice) return $this->fail(null, 'Hóa đơn không tồn tại', 400);
        $invoice->delete();
        return $this->json(null, 'Xóa hóa đơn thành công', 200);
    }
}
