<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Middleware;

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
}
