<?php

namespace App\Http\Controllers;

use App\Enums\{UserRole, MedicineStatus, InvoiceStatus, OrderStatus};
use App\Models\{Order, Invoice, User, Medicine};
use Carbon\Carbon;
use Illuminate\Http\Request;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Middleware;

#[Prefix('v1/admin/statistics')]
#[Middleware(['jwt.auth',])]
/**
 * @OA\Tag(
 *     name="Dashboard",
 *     description="Các API endpoint để quản lý thống kê tổng quan dashboard cho quản trị viên",
 * )
 */
class StatisticsController extends Controller
{
    #[Get(uri: 'overview', name: 'admin.statistics.overview')]
    /**
     * @OA\Get(
     *     path="/v1/admin/statistics/dashboard",
     *     summary="Lấy thống kê tổng quan dashboard",
     *     description="Truy xuất thống kê tổng quan dashboard bao gồm dữ liệu đơn hàng, doanh thu, người dùng và thuốc",
     *     operationId="getDashboardStats",
     *     tags={"Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lấy thống kê dashboard thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lấy thống kê dashboard thành công"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="overview",
     *                     type="object",
     *                     description="Thống kê tổng quan",
     *                     @OA\Property(
     *                         property="total_orders",
     *                         type="object",
     *                         description="Thống kê đơn hàng",
     *                         @OA\Property(property="total", type="integer", example=1250, description="Tổng số đơn hàng"),
     *                         @OA\Property(property="pending", type="integer", example=45, description="Đơn hàng chờ xử lý"),
     *                         @OA\Property(property="completed", type="integer", example=1150, description="Đơn hàng hoàn thành"),
     *                         @OA\Property(property="cancelled", type="integer", example=55, description="Đơn hàng đã hủy")
     *                     ),
     *                     @OA\Property(property="total_revenue", type="number", format="float", example=125000.50, description="Tổng doanh thu"),
     *                     @OA\Property(
     *                         property="total_users",
     *                         type="object",
     *                         description="Thống kê người dùng",
     *                         @OA\Property(property="total", type="integer", example=500, description="Tổng số khách hàng"),
     *                         @OA\Property(property="pharmacists", type="integer", example=25, description="Số dược sĩ"),
     *                         @OA\Property(property="customers", type="integer", example=500, description="Số khách hàng")
     *                     ),
     *                     @OA\Property(
     *                         property="total_medicines",
     *                         type="object",
     *                         description="Thống kê thuốc",
     *                         @OA\Property(property="total", type="integer", example=350, description="Tổng số loại thuốc"),
     *                         @OA\Property(property="in_stock", type="integer", example=320, description="Thuốc còn hàng"),
     *                         @OA\Property(property="out_of_stock", type="integer", example=30, description="Thuốc hết hàng")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="today_stats",
     *                     type="object",
     *                     description="Thống kê hôm nay",
     *                     @OA\Property(property="orders_today", type="integer", example=15, description="Đơn hàng hôm nay"),
     *                     @OA\Property(property="revenue_today", type="number", format="float", example=2500.75, description="Doanh thu hôm nay"),
     *                     @OA\Property(property="new_customers", type="integer", example=8, description="Khách hàng mới hôm nay")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Chưa xác thực - Token JWT không hợp lệ hoặc thiếu",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Truy cập không được phép")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Bị cấm - Yêu cầu quyền Admin",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Yêu cầu quyền Admin")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi máy chủ nội bộ",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Lỗi máy chủ nội bộ")
     *         )
     *     )
     * )
     */
    public function dashboardStats()
    {
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

    #[Get(uri: 'monthly-revenue', name: 'admin.statistics.monthly-revenue')]
    /**
     * @OA\Get(
     *     path="/v1/admin/statistics/monthly-revenue",
     *     summary="Lấy thống kê doanh thu theo tháng",
     *     description="Trả về doanh thu của 12 tháng trong năm để hiển thị biểu đồ",
     *     operationId="getMonthlyRevenue",
     *     tags={"Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="year",
     *         in="query",
     *         description="Năm cần thống kê (mặc định là năm hiện tại)",
     *         required=false,
     *         @OA\Schema(type="integer", example=2024)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lấy thống kê doanh thu theo tháng thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lấy thống kê doanh thu theo tháng thành công"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="month", type="string", example="T1"),
     *                     @OA\Property(property="revenue", type="number", example=45000000)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Chưa xác thực",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     */
    public function monthlyRevenue(Request $request)
    {
        $year = $request->get('y', Carbon::now()->year);
        $monthNames = [
            1 => 'T1',
            2 => 'T2',
            3 => 'T3',
            4 => 'T4',
            5 => 'T5',
            6 => 'T6',
            7 => 'T7',
            8 => 'T8',
            9 => 'T9',
            10 => 'T10',
            11 => 'T11',
            12 => 'T12'
        ];
        $monthlyRevenue = array_fill(1, 12, 0);
        $invoices = Invoice::where('status', InvoiceStatus::PAID)
            ->whereYear('created_at', $year)
            ->get(['total_price', 'created_at']);
        foreach ($invoices as $invoice) {
            $month = (int) $invoice->created_at->format('n');
            $monthlyRevenue[$month] += (float) $invoice->total_price;
        }
        $result = [];
        for ($month = 1; $month <= 12; $month++) {
            $result[] = [
                'month' => $monthNames[$month],
                'revenue' => $monthlyRevenue[$month]
            ];
        }
        return $this->json($result, 'Lấy doanh thu hàng tháng thành công', 200);
    }

    #[Get(uri: 'last-12-months-revenue', name: 'admin.statistics.last-12-months-revenue')]
    /**
     * @OA\Get(
     *     path="/v1/admin/statistics/last-12-months-revenue",
     *     summary="Lấy thống kê doanh thu 12 tháng gần nhất",
     *     description="Trả về doanh thu của 12 tháng gần nhất kể từ tháng hiện tại",
     *     operationId="getLast12MonthsRevenue",
     *     tags={"Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lấy thống kê 12 tháng gần nhất thành công"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="month", type="string", example="T1"),
     *                     @OA\Property(property="revenue", type="number", example=45000000)
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getLast12MonthsRevenue()
    {
        $now = Carbon::now();
        $result = [];

        // Lặp qua 12 tháng gần nhất
        for ($i = 11; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $monthName = 'T' . $date->month;

            // Lấy doanh thu của tháng này
            $revenue = Invoice::where('status', InvoiceStatus::PAID)
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('total_price');

            $result[] = [
                'month' => $monthName,
                'revenue' => (float) $revenue
            ];
        }

        return $this->json($result, 'Lấy thống kê 12 tháng gần nhất thành công', 200);
    }

    #[Get(uri: 'order-status', name: 'admin.statistics.order-status')]
    /**
     * @OA\Get(
     *     path="/v1/admin/statistics/order-status",
     *     summary="Lấy thống kê trạng thái đơn hàng",
     *     description="Trả về số lượng đơn hàng theo từng trạng thái để hiển thị biểu đồ",
     *     operationId="getOrderStatusStats",
     *     tags={"Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lấy thống kê trạng thái đơn hàng thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lấy thống kê trạng thái đơn hàng thành công"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="status", type="string", example="pending", description="Trạng thái đơn hàng"),
     *                     @OA\Property(property="count", type="integer", example=45, description="Số lượng đơn hàng")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Chưa xác thực - Token JWT không hợp lệ hoặc thiếu",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Truy cập không được phép")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Bị cấm - Yêu cầu quyền Admin",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Yêu cầu quyền Admin")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi máy chủ nội bộ",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Lỗi máy chủ nội bộ")
     *         )
     *     )
     * )
     */
    public function orderStatusStats()
    {
        $statuses = OrderStatus::cases();
        $result = [];
        foreach ($statuses as $status) {
            $count = Order::where('status', $status)->count();
            $result[] = [
                'status' => $status->value,
                'count' => $count
            ];
        }
        return $this->json($result, 'Lấy thống kê trạng thái đơn hàng thành công', 200);
    }

    #[Get('daily-revenue-calendar', "admin.statistics.daily-revenue-calendar")]
    /**
     * @OA\Get(
     *     path="/v1/admin/statistics/daily-revenue-calendar",
     *     summary="Lấy thống kê doanh thu và đơn hàng theo ngày",
     *     description="Trả về doanh thu và số lượng đơn hàng của các ngày trong tháng để hiển thị biểu đồ calendar",
     *     operationId="getDailyRevenueCalendar",
     *     tags={"Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="month",
     *         in="query",
     *         description="Tháng cần thống kê (1-12, mặc định là tháng hiện tại)",
     *         required=false,
     *         @OA\Schema(type="integer", example=6)
     *     ),
     *     @OA\Parameter(
     *         name="year",
     *         in="query",
     *         description="Năm cần thống kê (mặc định là năm hiện tại)",
     *         required=false,
     *         @OA\Schema(type="integer", example=2025)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lấy thống kê doanh thu theo ngày thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lấy thống kê doanh thu theo ngày thành công"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="date", type="string", example="01/06"),
     *                     @OA\Property(property="revenue", type="number", example=45000000),
     *                     @OA\Property(property="orders", type="integer", example=15)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Dữ liệu đầu vào không hợp lệ",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Tháng không hợp lệ")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Chưa xác thực",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     */
    public function dailyRevenueCalendar(Request $request)
    {
        $month = $request->get('m', Carbon::now()->month);
        $year = $request->get('y', Carbon::now()->year);
        if ($month < 1 || $month > 12) return $this->json([], "Tháng không hợp lệ", 400);
        if ($year < 2000 || $year > 2100) return $this->json([], "Năm không hợp lệ", 400);
        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;
        $dailyData = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dailyData[$day] = [
                'revenue' => 0,
                'orders' => 0,
            ];
        };
        $invoices = Invoice::where('status', InvoiceStatus::PAID)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->get(['total_price', 'created_at']);
        $orders = Order::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->get(['created_at']);
        foreach ($invoices as $invoice) {
            $day = (int) $invoice->created_at->format('j');
            $dailyData[$day]['revenue'] += (float) $invoice->total_price;
        }
        foreach ($orders as $order) {
            $day = (int) $order->created_at->format('j');
            $dailyData[$day]['orders']++;
        }
        $result = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $result[] = [
                'date' => sprintf('%02d/%02d', $day, $month),
                'revenue' => $dailyData[$day]['revenue'],
                'orders' => $dailyData[$day]['orders']
            ];
        }
        return $this->json($result, "Lấy thống kê doanh thu hàng ngày thành công", 200);
    }
}
