<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Medicine;
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
     *     path="/api/v1/store/medicines",
     *     summary="Lấy danh sách thuốc có phân trang",
     *     tags={"Store"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Số trang",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lấy danh sách thuốc thành công"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="string", example="65f43c2a8e5c5"),
     *                         @OA\Property(property="name", type="string", example="Paracetamol"),
     *                         @OA\Property(property="price", type="number", example=15000),
     *                         @OA\Property(property="category", type="object")
     *                     )
     *                 ),
     *                 @OA\Property(property="total", type="integer", example=50)
     *             )
     *         )
     *     )
     * )
     */
    #[Get('/medicines', name: 'store.medicines')]
    public function Medicines(Request $request)
    {
        // Lấy danh sách thuốc có phân trang
        $medicines = Medicine::with('category')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return $this->json($medicines, "Lấy danh sách thuốc thành công");
    }

    /**
     * @OA\Get(
     *     path="/api/v1/store/medicines/{id}/details",
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
        $medicine = Medicine::with('category')
            ->where('id', $id)
            ->first();
        if (!$medicine) return $this->json(null, "Không tìm thấy thuốc", 404);
        return $this->json($medicine, "Lấy thông tin thuốc thành công");
    }

    /**
     * @OA\Get(
     *     path="/api/v1/store/categories",
     *     summary="Lấy tất cả danh mục",
     *     tags={"Store"},
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
     *                     @OA\Property(property="description", type="string")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    #[Get('/categories', name: 'store.categories')]
    public function RootCategories()
    {
        $categories = Category::all();
        return $this->json($categories, "Lấy toàn bộ danh mục thành công");
    }

    /**
     * @OA\Get(
     *     path="/api/v1/store/popular-medicine",
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
}

