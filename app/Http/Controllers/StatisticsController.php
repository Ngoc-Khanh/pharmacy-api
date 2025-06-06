<?php

namespace App\Http\Controllers;

use App\Enums\{UserRole, MedicineStatus, InvoiceStatus, OrderStatus};
use App\Models\{Order, Invoice, User, Medicine};
use Carbon\Carbon;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Middleware;

#[Prefix('v1/admin/statistics')]
#[Middleware(['jwt.auth', ])]
class StatisticsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/admin/statistics/dashboard",
     *     summary="Get dashboard statistics",
     *     description="Retrieve comprehensive dashboard statistics including orders, revenue, users, and medicines data",
     *     operationId="getDashboardStats",
     *     tags={"Admin Statistics"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Dashboard statistics retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lấy thống kê dashboard thành công"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="overview",
     *                     type="object",
     *                     @OA\Property(
     *                         property="total_orders",
     *                         type="object",
     *                         @OA\Property(property="total", type="integer", example=1250),
     *                         @OA\Property(property="pending", type="integer", example=45),
     *                         @OA\Property(property="completed", type="integer", example=1150),
     *                         @OA\Property(property="cancelled", type="integer", example=55)
     *                     ),
     *                     @OA\Property(property="total_revenue", type="number", format="float", example=125000.50),
     *                     @OA\Property(
     *                         property="total_users",
     *                         type="object",
     *                         @OA\Property(property="total", type="integer", example=500),
     *                         @OA\Property(property="pharmacists", type="integer", example=25),
     *                         @OA\Property(property="customers", type="integer", example=500)
     *                     ),
     *                     @OA\Property(
     *                         property="total_medicines",
     *                         type="object",
     *                         @OA\Property(property="total", type="integer", example=350),
     *                         @OA\Property(property="in_stock", type="integer", example=320),
     *                         @OA\Property(property="out_of_stock", type="integer", example=30)
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="today_stats",
     *                     type="object",
     *                     @OA\Property(property="orders_today", type="integer", example=15),
     *                     @OA\Property(property="revenue_today", type="number", format="float", example=2500.75),
     *                     @OA\Property(property="new_customers", type="integer", example=8)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid or missing JWT token",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized access")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Admin access required",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Admin access required")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
     * )
     */
    #[Get(uri: 'dashboard', name: 'admin.statistics.dashboard')]
    public function dashboardStats() {
        $today = Carbon::today();
        $overview = [
            'total_orders' => [
                'total' => Order::count(),
                'pending' => Order::where('status', OrderStatus::PENDING)->count(),
                'completed' => Order::where('status', OrderStatus::COMPLETED)->count(),
                'cancelled' => Order::where('status', OrderStatus::CANCELLED)->count(),
            ],
            'total_revenue' => Invoice::where('status', InvoiceStatus::PAID)->sum('total_price'),
            'total_users' => [
                'total' => User::where('role', UserRole::CUSTOMER)->count(),
                'pharmacists' => User::where('role', UserRole::PHARMACIST)->count(),
                'customers' => User::where('role', UserRole::CUSTOMER)->count(),
            ],
            'total_medicines' => [
                'total' => Medicine::count(),
                'in_stock' => Medicine::where('variants.stock_status', MedicineStatus::IN_STOCK)->count(),
                'out_of_stock' => Medicine::where('variants.stock_status', MedicineStatus::OUT_OF_STOCK)->count(),
            ],
        ];
        $todayStats = [
            'orders_today' => Order::whereDate('created_at', $today)->count(),
            'revenue_today' => Invoice::where('status', InvoiceStatus::PAID)
                ->whereDate('created_at', $today)->sum('total_price'),
            'new_customers' => User::where('role', UserRole::CUSTOMER)
                ->whereDate('created_at', $today)->count()
        ];
        return $this->json([
            'overview' => $overview,
            'today_stats' => $todayStats,
        ], 'Lấy thống kê dashboard thành công', 200);
    }
}
