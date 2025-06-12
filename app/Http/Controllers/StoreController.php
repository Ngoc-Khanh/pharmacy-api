<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Enums\MedicineStatus;
use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Invoice;
use App\Models\Medicine;
use App\Models\Order;
use Illuminate\Http\Request;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Patch;
use Spatie\RouteAttributes\Attributes\Prefix;

/**
 * @OA\Tag(
 *     name="Store",
 *     description="Các API cho Pharmacity Store"
 * )
 */
#[Prefix('v1/store')]
class StoreController extends Controller
{
    /**
     * @OA\Get(
     *     path="/v1/store/medicines",
     *     summary="Lấy danh sách thuốc với phân trang và bộ lọc nâng cao",
     *     description="API để lấy danh sách thuốc có hỗ trợ tìm kiếm, lọc theo danh mục, giá, đánh giá và sắp xếp",
     *     tags={"Store"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Số trang hiện tại",
     *         required=false,
     *         @OA\Schema(type="integer", default=1, minimum=1, example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Số lượng thuốc trên mỗi trang (tối đa 100)",
     *         required=false,
     *         @OA\Schema(type="integer", default=20, minimum=1, maximum=100, example=20)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Từ khóa tìm kiếm theo tên, slug hoặc mô tả thuốc",
     *         required=false,
     *         @OA\Schema(type="string", example="paracetamol")
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="ID danh mục hoặc nhiều ID phân cách bằng dấu phẩy",
     *         required=false,
     *         @OA\Schema(type="string", example="1,2,3")
     *     ),
     *     @OA\Parameter(
     *         name="category_slug",
     *         in="query",
     *         description="Slug danh mục hoặc nhiều slug phân cách bằng dấu phẩy",
     *         required=false,
     *         @OA\Schema(type="string", example="giam-dau,khang-sinh")
     *     ),
     *     @OA\Parameter(
     *         name="min_price",
     *         in="query",
     *         description="Giá tối thiểu (VNĐ)",
     *         required=false,
     *         @OA\Schema(type="number", minimum=0, example=10000)
     *     ),
     *     @OA\Parameter(
     *         name="max_price",
     *         in="query",
     *         description="Giá tối đa (VNĐ)",
     *         required=false,
     *         @OA\Schema(type="number", minimum=0, example=500000)
     *     ),
     *     @OA\Parameter(
     *         name="min_rating",
     *         in="query",
     *         description="Đánh giá tối thiểu (từ 0-5 sao)",
     *         required=false,
     *         @OA\Schema(type="number", minimum=0, maximum=5, example=4.0)
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Trạng thái tồn kho (có thể nhiều trạng thái phân cách bằng dấu phẩy)",
     *         required=false,
     *         @OA\Schema(
     *             type="string", 
     *             enum={"IN-STOCK", "OUT-OF-STOCK"}, 
     *             example="IN-STOCK"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Tiêu chí sắp xếp danh sách thuốc",
     *         required=false,
     *         @OA\Schema(
     *             type="string", 
     *             enum={"price_asc", "price_desc", "name_asc", "name_desc", "rating_desc", "newest", "oldest"}, 
     *             default="newest",
     *             example="price_asc"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lấy danh sách thuốc thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lấy danh sách thuốc thành công"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="per_page", type="integer", example=20),
     *                 @OA\Property(property="total", type="integer", example=150),
     *                 @OA\Property(property="last_page", type="integer", example=8),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="to", type="integer", example=20),
     *                 @OA\Property(property="path", type="string", example="/v1/store/medicines"),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="string", example="65f43c2a8e5c5"),
     *                         @OA\Property(property="name", type="string", example="Paracetamol 500mg"),
     *                         @OA\Property(property="slug", type="string", example="paracetamol-500mg"),
     *                         @OA\Property(property="description", type="string", example="Thuốc giảm đau, hạ sốt"),
     *                         @OA\Property(property="price", type="number", example=15000),
     *                         @OA\Property(property="stock", type="integer", example=100),
     *                         @OA\Property(property="image_url", type="string", example="https://example.com/image.jpg"),
     *                         @OA\Property(
     *                             property="ratings",
     *                             type="object",
     *                             @OA\Property(property="star", type="number", example=4.5),
     *                             @OA\Property(property="liked", type="integer", example=125)
     *                         ),
     *                         @OA\Property(
     *                             property="category",
     *                             type="object",
     *                             @OA\Property(property="id", type="string", example="cat_001"),
     *                             @OA\Property(property="name", type="string", example="Giảm đau"),
     *                             @OA\Property(property="slug", type="string", example="giam-dau")
     *                         ),
     *                         @OA\Property(
     *                             property="supplier",
     *                             type="object",
     *                             @OA\Property(property="id", type="string", example="sup_001"),
     *                             @OA\Property(property="name", type="string", example="Dược phẩm ABC"),
     *                             @OA\Property(property="email", type="string", example="contact@abc.com")
     *                         ),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-20T14:25:00Z")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="links",
     *                     type="object",
     *                     @OA\Property(property="first", type="string", example="/v1/store/medicines?page=1"),
     *                     @OA\Property(property="last", type="string", example="/v1/store/medicines?page=8"),
     *                     @OA\Property(property="prev", type="string", nullable=true, example=null),
     *                     @OA\Property(property="next", type="string", example="/v1/store/medicines?page=2")
     *                 )
     *             ),
     *             @OA\Property(property="status", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Tham số không hợp lệ",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Tham số per_page không được vượt quá 100"),
     *             @OA\Property(property="data", type="null", example=null),
     *             @OA\Property(property="status", type="integer", example=400)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi server",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Có lỗi xảy ra khi lấy danh sách thuốc"),
     *             @OA\Property(property="data", type="null", example=null),
     *             @OA\Property(property="status", type="integer", example=500)
     *         )
     *     )
     * )
     */
    #[Get('/medicines', name: 'store.medicines')]
    public function Medicines(Request $request)
    {
        $perPage = min(max((int)$request->query('per_page', 20), 1), 100);
        $query = Medicine::with(['category', 'supplier']);
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }
        if ($categoryId = $request->query('category')) {
            if (is_string($categoryId) && str_contains($categoryId, ',')) {
                $categoryIds = array_map('intval', explode(',', $categoryId));
                $query->whereIn('category_id', $categoryIds);
            } else {
                $query->where('category_id', $categoryId);
            }
        }
        if ($categorySlug = $request->query('category_slug')) {
            if (is_string($categorySlug) && str_contains($categorySlug, ',')) {
                $categorySlugs = array_map('trim', explode(',', $categorySlug));
                $query->whereHas('category', function ($q) use ($categorySlugs) {
                    $q->whereIn('slug', $categorySlugs);
                });
            } else {
                $query->whereHas('category', function ($q) use ($categorySlug) {
                    $q->where('slug', $categorySlug);
                });
            }
        }
        if ($minPrice = $request->query('min_price')) $query->where('price', '>=', (float)$minPrice);
        if ($maxPrice = $request->query('max_price')) $query->where('price', '<=', (float)$maxPrice);
        if ($minRating = $request->query('min_rating')) $query->where('ratings.star', '>=', (float)$minRating);
        if ($status = $request->query('status')) {
            if (is_string($status) && str_contains($status, ',')) {
                $statuses = array_map('trim', explode(',', $status));
                $query->where(function ($q) use ($statuses) {
                    foreach ($statuses as $singleStatus) {
                        switch (strtoupper($singleStatus)) {
                            case MedicineStatus::IN_STOCK->value:
                                $q->orWhere('stock', '>', 10);
                                break;
                            case MedicineStatus::OUT_OF_STOCK->value:
                                $q->orWhere('stock', '<=', 0);
                                break;
                        }
                    }
                });
            } else {
                switch (strtoupper($status)) {
                    case MedicineStatus::IN_STOCK->value:
                        $query->where('stock', '>', 10);
                        break;
                    case MedicineStatus::OUT_OF_STOCK->value:
                        $query->where('stock', '<=', 0);
                        break;
                }
            }
        }
        $sortBy = $request->query('sort_by', 'newest');
        switch ($sortBy) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'rating_desc':
                $query->orderBy('ratings.star', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }
        $medicines = $query->paginate($perPage);
        return $this->json($medicines, "Lấy danh sách thuốc thành công");
    }

    /**
     * @OA\Get(
     *     path="/v1/store/medicines/{id}/details",
     *     summary="Lấy chi tiết thuốc theo ID",
     *     tags={"Store"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID thuốc",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lấy thông tin thuốc thành công"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", example="65f43c2a8e5c5"),
     *                 @OA\Property(property="name", type="string", example="Paracetamol"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="price", type="number", example=15000),
     *                 @OA\Property(property="category", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy thuốc",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Không tìm thấy thuốc"),
     *             @OA\Property(property="data", type="null", example=null)
     *         )
     *     )
     * )
     */
    #[Get('/medicines/{id}/details', name: 'store.medicines.show')]
    public function MedicineDetails($id)
    {
        // Lấy chi tiết thuốc theo ID
        $medicine = Medicine::with(['category', 'supplier'])
            ->where('id', $id)
            ->first();
        if (!$medicine) return $this->json(null, "Không tìm thấy thuốc", 404);
        return $this->json($medicine, "Lấy thông tin thuốc thành công");
    }

    #[Get('/categories', name: 'store.categories')]
    /**
     * @OA\Get(
     *     path="/v1/store/categories",
     *     summary="Lấy tất cả danh mục",
     *     tags={"Store"},
     *     @OA\Parameter(
     *         name="s",
     *         in="query",
     *         description="Từ khóa tìm kiếm theo tên hoặc slug",
     *         required=false,
     *         @OA\Schema(type="string", example="giam-dau")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lấy toàn bộ danh mục thành công"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="string", example="65f43c2a8e5c5"),
     *                     @OA\Property(property="name", type="string", example="Giảm đau"),
     *                     @OA\Property(property="slug", type="string", example="giam-dau"),
     *                     @OA\Property(property="description", type="string", example="Các loại thuốc giảm đau"),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function RootCategories(Request $request)
    {
        $search = $request->query('s');
        $categories = Category::query()
            ->when($search, function ($query) use ($search) {
                $query->where('slug', 'like', '%' . $search . '%')
                    ->orWhere('name', 'like', '%' . $search . '%');
            })
            ->get();

        return $this->json($categories, "Lấy toàn bộ danh mục thành công");
    }

    /**
     * @OA\Get(
     *     path="/v1/store/popular-medicine",
     *     summary="Lấy top 4 thuốc phổ biến nhất",
     *     tags={"Store"},
     *     @OA\Response(
     *         response=200,
     *         description="Thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lấy 4 sản phẩm thuốc có lượt thích và đánh giá cao nhất thành công"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="string", example="65f43c2a8e5c5"),
     *                     @OA\Property(property="name", type="string", example="Paracetamol"),
     *                     @OA\Property(property="price", type="number", example=15000),
     *                     @OA\Property(property="ratings", type="object",
     *                         @OA\Property(property="liked", type="integer", example=150),
     *                         @OA\Property(property="star", type="number", example=4.8)
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    #[Get('/popular-medicine', name: 'store.popular-medicine')]
    public function PopularMedicine()
    {
        // Lấy 4 thuốc có lượt thích và đánh giá cao nhất
        $medicines = Medicine::orderBy('ratings.liked', 'desc')
            ->orderBy('ratings.star', 'desc')
            ->limit(4)
            ->get();

        return $this->json($medicines, "Lấy 4 sản phẩm thuốc có lượt thích và đánh giá cao nhất thành công");
    }

    /**
     * @OA\Get(
     *     path="/v1/store/deliver/orders",
     *     operationId="getOrdersDeliver",
     *     tags={"Store"},
     *     summary="Lấy danh sách đơn hàng cho người giao hàng",
     *     description="Lấy danh sách đơn hàng đang chờ xác nhận, đang vận chuyển, hoặc đang giao hàng",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="_id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                     @OA\Property(property="user_id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                     @OA\Property(property="status", type="string", example="PENDING"),
     *                     @OA\Property(property="items", type="array", 
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="medicine_id", type="string"),
     *                             @OA\Property(property="name", type="string"),
     *                             @OA\Property(property="quantity", type="integer"),
     *                             @OA\Property(property="price", type="number"),
     *                             @OA\Property(property="item_total", type="number"),
     *                             @OA\Property(property="medicine", type="object")
     *                         )
     *                     ),
     *                     @OA\Property(property="sub_total", type="number", example=30000),
     *                     @OA\Property(property="shipping_fee", type="number", example=15000),
     *                     @OA\Property(property="discount", type="number", example=0),
     *                     @OA\Property(property="total_price", type="number", example=45000),
     *                     @OA\Property(property="shipping_address", type="object"),
     *                     @OA\Property(property="payment_method", type="string", example="COD"),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="user", type="object")
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Lấy danh sách đơn hàng thành công"),
     *             @OA\Property(property="status", type="integer", example=200)
     *         )
     *     )
     * )
     */
    #[Get(uri: "/deliver/orders", name: "deliver.orders.get")]
    public function getOrdersDeliver()
    {
        $orders = Order::whereIn('status', [OrderStatus::PENDING, OrderStatus::PROCESSING, OrderStatus::SHIPPED, OrderStatus::DELIVERED])->get();
        $orders->map(function ($order) {
            $order->items = collect($order->items)->map(function ($item) {
                $item['medicine'] = Medicine::find($item['medicine_id']);
                return $item;
            });
        });
        $orders->load('user');
        return $this->json($orders, 'Lấy danh sách đơn hàng thành công', 200);
    }

    #[Post(uri: "/deliver/orders/{id}/update-status", name: "deliver.orders.updateStatus")]
    /**
     * @OA\Post(
     *     path="/v1/store/deliver/orders/{id}/update-status",
     *     operationId="updateDeliveryOrderStatus",
     *     tags={"Store"},
     *     summary="Cập nhật trạng thái đơn hàng cho người giao hàng",
     *     description="Cập nhật trạng thái đơn hàng (PENDING, PROCESSING, SHIPPED, DELIVERED, CANCELLED)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID đơn hàng",
     *         required=true,
     *         @OA\Schema(type="string", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Thông tin trạng thái mới",
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 enum={"PENDING", "PROCESSING", "SHIPPED", "DELIVERED", "CANCELLED"},
     *                 example="SHIPPED",
     *                 description="Trạng thái mới của đơn hàng"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cập nhật thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Cập nhật trạng thái đơn hàng thành công"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="_id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="user_id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="status", type="string", example="SHIPPED"),
     *                 @OA\Property(property="items", type="array", 
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="medicine_id", type="string"),
     *                         @OA\Property(property="name", type="string"),
     *                         @OA\Property(property="quantity", type="integer"),
     *                         @OA\Property(property="price", type="number"),
     *                         @OA\Property(property="item_total", type="number")
     *                     )
     *                 ),
     *                 @OA\Property(property="sub_total", type="number", example=30000),
     *                 @OA\Property(property="shipping_fee", type="number", example=15000),
     *                 @OA\Property(property="discount", type="number", example=0),
     *                 @OA\Property(property="total_price", type="number", example=45000),
     *                 @OA\Property(property="shipping_address", type="object"),
     *                 @OA\Property(property="payment_method", type="string", example="COD"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time"),
     *                 @OA\Property(property="created_at", type="string", format="date-time")
     *             ),
     *             @OA\Property(property="status", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Đơn hàng không tồn tại",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Đơn hàng không tồn tại"),
     *             @OA\Property(property="data", type="null", example=null),
     *             @OA\Property(property="status", type="integer", example=404)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Dữ liệu không hợp lệ",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Trạng thái không hợp lệ"),
     *             @OA\Property(property="data", type="null", example=null),
     *             @OA\Property(property="status", type="integer", example=400)
     *         )
     *     )
     * )
     */
    public function updateOrderStatus($id, Request $request)
    {
        $order = Order::find($id);
        if (!$order) return $this->json(null, 'Đơn hàng không tồn tại', 404);
        $order->status = $request->status;
        $order->save();
        if ($request->status == OrderStatus::COMPLETED->value) {
            $invoice = Invoice::where('order_id', $id)->first();
            if ($invoice) {
                $invoice->status = InvoiceStatus::PAID->value;
                $invoice->save();
            }
        } else if ($request->status == OrderStatus::CANCELLED->value) {
            $invoice = Invoice::where('order_id', $id)->first();
            if ($invoice) $invoice->delete();
        }
        return $this->json($order, 'Cập nhật trạng thái đơn hàng thành công', 200);
    }
}
