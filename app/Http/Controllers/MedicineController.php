<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Utils\ImageUtils;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Requests\MedicineRequest;
use Illuminate\Support\Facades\Validator;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Patch;
use Spatie\RouteAttributes\Attributes\Delete;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Middleware;

#[Prefix(prefix: 'v1/admin/medicines')]
#[Middleware(middleware: ['jwt.auth', 'role:admin,pharmacist'])]
/**
 * @OA\Tag(
 *     name="Medicines",
 *     description="Các API endpoint để quản lý sản phẩm dược phẩm"
 * )
 */
class MedicineController extends Controller
{
    /**
     * @OA\Get(
     *     path="/v1/admin/medicines",
     *     summary="Lấy danh sách thuốc",
     *     description="Truy xuất tất cả các thuốc cùng với thông tin danh mục và nhà cung cấp liên quan",
     *     operationId="listMedicine",
     *     tags={"Medicines"},
     *     security={{"bearer_token":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Danh sách thuốc",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="message", type="string", example="Danh sách thuốc"),
     *             @OA\Property(property="status", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy thuốc",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Không tìm thấy thuốc"),
     *             @OA\Property(property="status", type="integer", example=404)
     *         )
     *     )
     * )
     */
    #[Get(uri: "/", name: "admin.medicines.index")]
    public function listMedicine()
    {
        $medicines = Medicine::with(['category', 'supplier'])->get();
        if ($medicines->isEmpty()) return $this->fail(null, 'Không tìm thấy thuốc', 404);
        return $this->json($medicines, 'Danh sách thuốc', 200);
    }

    #[Post(uri: '/add', name: 'admin.medicines.add')]
    /**
     * @OA\Post(
     *     path="/v1/admin/medicines/add",
     *     summary="Thêm thuốc mới",
     *     description="Tạo một sản phẩm thuốc mới với thông tin chi tiết",
     *     operationId="addMedicine",
     *     tags={"Medicines"},
     *     security={{"bearer_token":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name", "category_id", "supplier_id"},
     *                 @OA\Property(property="category_id", type="integer", description="ID của danh mục thuốc"),
     *                 @OA\Property(property="supplier_id", type="integer", description="ID của nhà cung cấp"),
     *                 @OA\Property(property="name", type="string", description="Tên thuốc"),
     *                 @OA\Property(property="thumbnail", type="file", description="Hình ảnh thuốc"),
     *                 @OA\Property(property="description", type="string", description="Mô tả thuốc"),
     *                 @OA\Property(
     *                     property="variants",
     *                     type="object",
     *                     @OA\Property(property="price", type="number", description="Giá bán"),
     *                     @OA\Property(property="limit_quantity", type="integer", description="Số lượng giới hạn"),
     *                     @OA\Property(property="stock_status", type="string", description="Trạng thái tồn kho"),
     *                     @OA\Property(property="original_price", type="number", description="Giá gốc"),
     *                     @OA\Property(property="discount_percent", type="number", description="Phần trăm giảm giá"),
     *                     @OA\Property(property="is_featured", type="boolean", description="Có phải sản phẩm nổi bật"),
     *                     @OA\Property(property="is_active", type="boolean", description="Trạng thái hoạt động")
     *                 ),
     *                 @OA\Property(
     *                     property="details",
     *                     type="object",
     *                     @OA\Property(property="ingredients", type="string", description="Thành phần"),
     *                     @OA\Property(property="usage", type="string", description="Cách dùng"),
     *                     @OA\Property(
     *                         property="paramaters",
     *                         type="object",
     *                         @OA\Property(property="origin", type="string", description="Xuất xứ"),
     *                         @OA\Property(property="packaging", type="string", description="Quy cách đóng gói")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="usageguide",
     *                     type="object",
     *                     @OA\Property(
     *                         property="dosage",
     *                         type="object",
     *                         @OA\Property(property="adult", type="string", description="Liều dùng cho người lớn"),
     *                         @OA\Property(property="child", type="string", description="Liều dùng cho trẻ em")
     *                     ),
     *                     @OA\Property(property="directions", type="string", description="Hướng dẫn sử dụng"),
     *                     @OA\Property(property="precautions", type="string", description="Cảnh báo và thận trọng")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Thêm thuốc thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Đã thêm thuốc thành công"),
     *             @OA\Property(property="status", type="integer", example=201)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Dữ liệu không hợp lệ",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi server",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Không thể tải ảnh"),
     *             @OA\Property(property="status", type="integer", example=500)
     *         )
     *     )
     * )
     */
    public function addMedicine(MedicineRequest $request)
    {
        $validated = $request->validated();
        $slug = Str::slug($request->name);
        $thumbnailData = [
            'url' => null,
            'alt' => $slug . '-alt',
        ];
        if ($request->hasFile('thumbnail')) {
            $imageUtils = new ImageUtils();
            $uploadResult = $imageUtils->uploadImage(
                $request->file('thumbnail'),
                'medicines',
            );
            if (!$uploadResult['success']) return $this->fail(null, 'Không thể tải ảnh: ' . $uploadResult['message'], 500);
            $thumbnailData['url'] = $uploadResult['url'];
        }
        $data = Medicine::create([
            'category_id' => $validated['category_id'],
            'supplier_id' => $validated['supplier_id'],
            'name' => $validated['name'],
            'slug' => $slug,
            'thumbnail' => $thumbnailData,
            'description' => $validated['description'],
            'variants' => [
                'price' => $validated['variants']['price'],
                'limit_quantity' => $validated['variants']['limit_quantity'],
                'stock_status' => $validated['variants']['stock_status'],
                'original_price' => $validated['variants']['original_price'],
                'discount_percent' => $validated['variants']['discount_percent'],
                'is_featured' => $validated['variants']['is_featured'],
                'is_active' => $validated['variants']['is_active'],
            ],
            'details' => [
                'ingredients' => $validated['details']['ingredients'],
                'usage' => $validated['details']['usage'],
                'paramaters' => [
                    'origin' => $validated['details']['paramaters']['origin'],
                    'packaging' => $validated['details']['paramaters']['packaging'],
                ],
            ],
            'usageguide' => [
                'dosage' => [
                    'adult' => $validated['usageguide']['dosage']['adult'],
                    'child' => $validated['usageguide']['dosage']['child'],
                ],
                'directions' => $validated['usageguide']['directions'],
                'precautions' => $validated['usageguide']['precautions'],
            ],
        ]);
        return $this->json($data, 'Đã thêm thuốc thành công', 201);
    }

    #[Patch(uri: '/update/{id}', name: 'admin.medicines.update')]
    /**
     * @OA\Patch(
     *     path="/v1/admin/medicines/update/{id}",
     *     summary="Cập nhật thông tin thuốc",
     *     description="Cập nhật thông tin của một thuốc theo ID",
     *     operationId="updateMedicine",
     *     tags={"Medicines"},
     *     security={{"bearer_token":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của thuốc cần cập nhật",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Thông tin thuốc cần cập nhật",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="category_id", type="string", description="ID danh mục"),
     *             @OA\Property(property="supplier_id", type="string", description="ID nhà cung cấp"),
     *             @OA\Property(property="name", type="string", description="Tên thuốc"),
     *             @OA\Property(property="description", type="string", description="Mô tả thuốc"),
     *             @OA\Property(
     *                 property="variants",
     *                 type="object",
     *                 @OA\Property(property="price", type="number", description="Giá bán"),
     *                 @OA\Property(property="limit_quantity", type="integer", description="Giới hạn số lượng"),
     *                 @OA\Property(property="stock_status", type="string", enum={"IN-STOCK", "OUT-OF-STOCK", "PRE-ORDER"}, description="Trạng thái tồn kho"),
     *                 @OA\Property(property="original_price", type="number", description="Giá gốc"),
     *                 @OA\Property(property="discount_percent", type="number", description="Phần trăm giảm giá"),
     *                 @OA\Property(property="is_featured", type="boolean", description="Có phải sản phẩm nổi bật"),
     *                 @OA\Property(property="is_active", type="boolean", description="Trạng thái hoạt động")
     *             ),
     *             @OA\Property(
     *                 property="details",
     *                 type="object",
     *                 @OA\Property(property="ingredients", type="string", description="Thành phần"),
     *                 @OA\Property(property="usage", type="array", @OA\Items(type="string"), description="Cách dùng"),
     *                 @OA\Property(
     *                     property="paramaters",
     *                     type="object",
     *                     @OA\Property(property="origin", type="string", description="Xuất xứ"),
     *                     @OA\Property(property="packaging", type="string", description="Quy cách đóng gói")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="usageguide",
     *                 type="object",
     *                 @OA\Property(
     *                     property="dosage",
     *                     type="object",
     *                     @OA\Property(property="adult", type="string", description="Liều dùng cho người lớn"),
     *                     @OA\Property(property="child", type="string", description="Liều dùng cho trẻ em")
     *                 ),
     *                 @OA\Property(property="directions", type="array", @OA\Items(type="string"), description="Hướng dẫn sử dụng"),
     *                 @OA\Property(property="precautions", type="array", @OA\Items(type="string"), description="Cảnh báo và thận trọng")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cập nhật thuốc thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Đã cập nhật thuốc thành công"),
     *             @OA\Property(property="status", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy thuốc",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Không tìm thấy thuốc"),
     *             @OA\Property(property="status", type="integer", example=404)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Dữ liệu không hợp lệ",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Dữ liệu không hợp lệ"),
     *             @OA\Property(property="status", type="integer", example=422)
     *         )
     *     )
     * )
     */
    public function updateMedicine(Request $request, $id)
    {
        $medicine = Medicine::find($id);
        if (!$medicine) return $this->fail(null, 'Không tìm thấy thuốc', 404);
        $validator = Validator::make($request->all(), [
            'category_id' => 'nullable|string|exists:categories,_id',
            'supplier_id' => 'nullable|string|exists:suppliers,_id',
            'name' => 'nullable|string|min:3|max:255',
            'description' => 'nullable|string|min:3|max:1000',
            'variants.price' => 'nullable|numeric|min:0',
            'variants.limit_quantity' => 'nullable|integer|min:0',
            'variants.stock_status' => 'nullable|string|in:IN-STOCK,OUT-OF-STOCK,PRE-ORDER',
            'variants.original_price' => 'nullable|numeric|min:0',
            'variants.discount_percent' => 'nullable|numeric|min:0|max:100',
            'variants.is_featured' => 'nullable|boolean',
            'variants.is_active' => 'nullable|boolean',
            'details.ingredients' => 'nullable|string|min:3|max:1000',
            'details.usage' => 'nullable|array',
            'details.usage.*' => 'nullable|string|min:2|max:255',
            'details.paramaters.origin' => 'nullable|string|min:3|max:50',
            'details.paramaters.packaging' => 'nullable|string|min:3|max:100',
            'usageguide.dosage.adult' => 'nullable|string|min:3|max:100',
            'usageguide.dosage.child' => 'nullable|string|min:3|max:100',
            'usageguide.directions' => 'nullable|array',
            'usageguide.directions.*' => 'nullable|string|min:2|max:255',
            'usageguide.precautions' => 'nullable|array',
            'usageguide.precautions.*' => 'nullable|string|min:2|max:255',
        ]);
        if ($validator->fails()) return $this->fail(null, 'Dữ liệu không hợp lệ', 422);
        $updateData = array_filter($request->only([
            'category_id',
            'supplier_id',
            'name',
            'description',
            'variants',
            'details',
            'usageguide',
        ]), function ($value) {
            return !is_null($value);
        });
        $medicine->update($updateData);
        return $this->json($medicine, 'Đã cập nhật thuốc thành công', 200);
    }

    #[Delete('/delete/{id}', name: 'admin.medicines.delete')]
    /**
     * @OA\Delete(
     *     path="/v1/admin/medicines/delete/{id}",
     *     summary="Xóa một thuốc",
     *     description="Xóa một thuốc theo ID",
     *     operationId="deleteMedicine",
     *     tags={"Medicines"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của thuốc cần xóa",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Xóa thuốc thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Đã xóa thuốc thành công"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy thuốc",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Không tìm thấy thuốc"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     )
     * )
     */
    public function deleteMedicine($id)
    {
        $medicine = Medicine::find($id);
        if (!$medicine) return $this->fail(null, 'Không tìm thấy thuốc', 404);
        $medicine->delete();
        return $this->json(null, 'Đã xóa thuốc thành công', 200);
    }
}
