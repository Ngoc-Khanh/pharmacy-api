<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Middleware;

#[Prefix('v1/admin/categories')]
#[Middleware('jwt.auth', 'role:admin,pharmacist')]
/**
 * @OA\Tag(
 *     name="Categories",
 *     description="Quản lý danh mục thuốc"
 * )
 */
class CategoryController extends Controller
{
    /**
     * @OA\Post(
     *     path="/v1/admin/categories/create",
     *     operationId="createCategory",
     *     tags={"Categories"},
     *     summary="Thêm danh mục mới",
     *     description="Tạo một danh mục mới trong hệ thống",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title"},
     *             @OA\Property(property="title", type="string", example="Thuốc kháng sinh", description="Tên danh mục"),
     *             @OA\Property(property="description", type="string", example="Các loại thuốc kháng sinh phổ biến", description="Mô tả chi tiết về danh mục")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tạo thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="_id", type="string", example="65f1b3fc5bce7125f4001ec2"),
     *                 @OA\Property(property="title", type="string", example="Thuốc kháng sinh"),
     *                 @OA\Property(property="slug", type="string", example="thuoc-khang-sinh"),
     *                 @OA\Property(property="description", type="string", example="Các loại thuốc kháng sinh phổ biến"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             ),
     *             @OA\Property(property="message", type="string", example="Danh mục đã được tạo thành công"),
     *             @OA\Property(property="status", type="integer", example=201),
     *             @OA\Property(property="locale", type="string", example="vi_VN"),
     *             @OA\Property(property="error", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Dữ liệu không hợp lệ",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Tên danh mục phải có ít nhất 3 ký tự"),
     *             @OA\Property(property="status", type="integer", example=422),
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
    #[Post('/create', name: 'category.create')]
    public function createCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|min:3|max:255',
            'description' => 'nullable|string|max:1000',
        ]);
        if ($validator->fails()) return $this->fail(null, $validator->errors()->first(), 422);
        $category = Category::create([
            'title' => $request->title,
            'slug' => \Illuminate\Support\Str::slug($request->title),
            'description' => $request->description,
        ]);
        return $this->json($category, 'Danh mục đã được tạo thành công', 201);
    }
}
