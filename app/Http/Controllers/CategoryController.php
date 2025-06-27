<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Patch;
use Spatie\RouteAttributes\Attributes\Delete;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Middleware;

#[Prefix(prefix: 'v1/admin/categories')]
#[Middleware(middleware: ['jwt.auth', 'role:admin,pharmacist'])]
/**
 * @OA\Tag(
 *     name="Categories",
 *     description="Quản lý danh mục thuốc"
 * )
 */
class CategoryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/v1/admin/categories",
     *     operationId="listCategories",
     *     tags={"Categories"},
     *     summary="Lấy danh sách danh mục",
     *     description="Trả về danh sách tất cả danh mục trong hệ thống",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", 
     *                @OA\Items(
     *                    @OA\Property(property="_id", type="string", example="65f1b3fc5bce7125f4001ec2"),
     *                    @OA\Property(property="title", type="string", example="Thuốc kháng sinh"),
     *                    @OA\Property(property="slug", type="string", example="thuoc-khang-sinh"),
     *                    @OA\Property(property="description", type="string", example="Các loại thuốc kháng sinh phổ biến"),
     *                    @OA\Property(property="is_active", type="boolean", example=true),
     *                    @OA\Property(property="created_at", type="string", format="date-time"),
     *                    @OA\Property(property="updated_at", type="string", format="date-time")
     *                )
     *             ),
     *             @OA\Property(property="message", type="string", example="Danh sách danh mục"),
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
    #[Get(uri: '/', name: 'category.list')]
    public function listCategories(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $sortField = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $search = $request->input('s', '');
        $allowedSortFields = ['title', 'created_at', 'updated_at'];
        if (!in_array($sortField, $allowedSortFields)) $sortField = 'created_at';
        $query = Category::query();
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            });
        }
        $categories = $query->orderBy($sortField, $sortOrder === 'asc' ? 'asc' : 'desc')->paginate($perPage);
        return $this->json($categories, 'Danh sách danh mục');
    }

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
     *             @OA\Property(property="description", type="string", example="Các loại thuốc kháng sinh phổ biến", description="Mô tả chi tiết về danh mục"),
     *             @OA\Property(property="is_active", type="boolean", example=true, description="Trạng thái kích hoạt của danh mục")
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
     *                 @OA\Property(property="is_active", type="boolean", example=true),
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
    #[Post(uri: '/create', name: 'category.create')]
    public function createCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|min:3|max:255',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);
        if ($validator->fails()) return $this->fail(null, $validator->errors()->first(), 422);
        $category = Category::create([
            'title' => $request->title,
            'slug' => \Illuminate\Support\Str::slug($request->title),
            'description' => $request->description,
            'is_active' => $request->has('is_active') ? $request->is_active : true,
            'created_at' => now(),
        ]);
        return $this->json($category, 'Danh mục đã được tạo thành công', 201);
    }

    /**
     * @OA\Patch(
     *     path="/v1/admin/categories/update/{id}",
     *     operationId="updateCategory",
     *     tags={"Categories"},
     *     summary="Cập nhật danh mục",
     *     description="Cập nhật thông tin danh mục trong hệ thống",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của danh mục cần cập nhật",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title"},
     *             @OA\Property(property="title", type="string", example="Thuốc kháng sinh", description="Tên danh mục"),
     *             @OA\Property(property="description", type="string", example="Các loại thuốc kháng sinh phổ biến", description="Mô tả chi tiết về danh mục"),
     *             @OA\Property(property="is_active", type="boolean", example=true, description="Trạng thái kích hoạt của danh mục")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cập nhật thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="_id", type="string", example="65f1b3fc5bce7125f4001ec2"),
     *                 @OA\Property(property="title", type="string", example="Thuốc kháng sinh"),
     *                 @OA\Property(property="slug", type="string", example="thuoc-khang-sinh"),
     *                 @OA\Property(property="description", type="string", example="Các loại thuốc kháng sinh phổ biến"),
     *                 @OA\Property(property="is_active", type="boolean", example=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             ),
     *             @OA\Property(property="message", type="string", example="Danh mục đã được cập nhật thành công"),
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="locale", type="string", example="vi_VN"),
     *             @OA\Property(property="error", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Danh mục không tồn tại",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Danh mục không tồn tại"),
     *             @OA\Property(property="status", type="integer", example=404),
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
    #[Patch(uri: '/update/{id}', name: 'category.update')]
    public function updateCategory(Request $request, $id)
    {
        $category = Category::find($id);
        if (!$category) return $this->fail(null, 'Danh mục không tồn tại', 404);
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|min:3|max:255',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);
        if ($validator->fails()) return $this->fail(null, $validator->errors()->first(), 422);
        $category->update([
            'title' => $request->title,
            'slug' => \Illuminate\Support\Str::slug($request->title),
            'description' => $request->description,
            'is_active' => $request->has('is_active') ? $request->is_active : $category->is_active,
        ]);
        return $this->json($category, 'Danh mục đã được cập nhật thành công', 200);
    }

    /**
     * @OA\Delete(
     *     path="/v1/admin/categories/delete/{id}",
     *     operationId="deleteCategory",
     *     tags={"Categories"},
     *     summary="Xóa danh mục",
     *     description="Xóa một danh mục trong hệ thống",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của danh mục cần xóa",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Xóa thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Danh mục đã được xóa thành công"),
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="locale", type="string", example="vi_VN"),
     *             @OA\Property(property="error", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Danh mục không tồn tại",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Danh mục không tồn tại"),
     *             @OA\Property(property="status", type="integer", example=404),
     *             @OA\Property(property="locale", type="string", example="vi_VN"),
     *             @OA\Property(property="error", type="object", nullable=true)
     *         )
     *     ),
     * )
     */
    #[Delete(uri: '/delete/{id}', name: 'category.delete')]
    public function deleteCategory($id)
    {
        $category = Category::find($id);
        if (!$category) return $this->fail(null, 'Danh mục không tồn tại', 404);
        $category->delete();
        return $this->json(null, 'Danh mục đã được xóa thành công', 200);
    }
}
