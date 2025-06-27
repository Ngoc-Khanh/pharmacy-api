<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Services\EmbeddingService;
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
    protected EmbeddingService $embeddingService;

    public function __construct(EmbeddingService $embeddingService)
    {
        $this->embeddingService = $embeddingService;
    }

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
    public function listMedicine(Request $request)
    {
        $query = Medicine::with(['category', 'supplier']);

        // Fix: Check for 'search' parameter instead of 's'
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%")
                    ->orWhere('details.ingredients', 'like', "%{$searchTerm}%")
                    // Add more searchable fields if needed
                    ->orWhereHas('category', function ($categoryQuery) use ($searchTerm) {
                        $categoryQuery->where('name', 'like', "%{$searchTerm}%");
                    })
                    ->orWhereHas('supplier', function ($supplierQuery) use ($searchTerm) {
                        $supplierQuery->where('name', 'like', "%{$searchTerm}%");
                    });
            });
        }

        // Filter by category
        if ($request->has('category_id') && !empty($request->category_id)) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by supplier
        if ($request->has('supplier_id') && !empty($request->supplier_id)) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Fix: Stock status filter - assuming variants is JSON or relation
        if ($request->has('stock_status') && !empty($request->stock_status)) {
            $query->whereJsonContains('variants->stock_status', $request->stock_status);
            // OR if variants is a relation:
            // $query->whereHas('variants', function ($variantQuery) use ($request) {
            //     $variantQuery->where('stock_status', $request->stock_status);
            // });
        }

        // Fix: Featured filter
        if ($request->has('is_featured')) {
            $query->whereJsonContains('variants->is_featured', $request->boolean('is_featured'));
            // OR if variants is a relation:
            // $query->whereHas('variants', function ($variantQuery) use ($request) {
            //     $variantQuery->where('is_featured', $request->boolean('is_featured'));
            // });
        }

        // Fix: Active filter
        if ($request->has('is_active')) {
            $query->whereJsonContains('variants->is_active', $request->boolean('is_active'));
            // OR if variants is a relation:
            // $query->whereHas('variants', function ($variantQuery) use ($request) {
            //     $variantQuery->where('is_active', $request->boolean('is_active'));
            // });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        // Fix: Handle JSON field sorting for variants
        $allowedSortFields = ['name', 'created_at'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        } elseif ($sortBy === 'price') {
            $query->orderBy('variants->price', $sortOrder);
        } elseif ($sortBy === 'original_price') {
            $query->orderBy('variants->original_price', $sortOrder);
        }

        $perPage = min($request->get('per_page', 20), 100); // Limit max per page
        $medicines = $query->paginate($perPage);

        if ($medicines->isEmpty()) {
            return $this->fail(null, 'Không tìm thấy thuốc', 404);
        }

        return $this->json($medicines, 'Danh sách thuốc', 200);
    }

    #[Get(uri: "/statistics", name: "admin.medicine.statistic", middleware: "role:admin")]
    public function getMedicineStatisticAdmin()
    {
        $totalMedicine = Medicine::count();
        $data = [
            'total_medicine' => $totalMedicine,
        ];
        return $this->json($data, 'Thống kê thuốc');
    }

    /**
     * @OA\Get(
     *     path="/v1/admin/medicines/{id}/details",
     *     summary="Lấy chi tiết thuốc",
     *     description="Truy xuất chi tiết của một thuốc theo ID",
     *     operationId="getMedicineDetails",
     *     tags={"Medicines"},
     *     security={{"bearer_token":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của thuốc cần lấy chi tiết",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Chi tiết thuốc",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Chi tiết thuốc"),
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
    #[Get(uri: '/{id}/details', name: 'admin.medicines.details')]
    public function getMedicineDetails($id)
    {
        $medicine = Medicine::with(['category', 'supplier'])->find($id);
        if (!$medicine) return $this->fail(null, 'Không tìm thấy thuốc', 404);
        return $this->json($medicine, 'Chi tiết thuốc', 200);
    }

    #[Post(uri: '/add', name: 'admin.medicines.add')]
    /**
     * @OA\Post(
     *     path="/v1/admin/medicines/add",
     *     summary="Add a new medicine",
     *     description="Create a new medicine product with detailed information",
     *     operationId="addMedicine",
     *     tags={"Medicines"},
     *     security={{"bearer_token":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name", "category_id", "supplier_id"},
     *                 @OA\Property(property="category_id", type="string", description="ID of the medicine category"),
     *                 @OA\Property(property="supplier_id", type="string", description="ID of the supplier"),
     *                 @OA\Property(property="name", type="string", description="Name of the medicine"),
     *                 @OA\Property(property="thumbnail", type="file", description="Medicine image"),
     *                 @OA\Property(property="description", type="string", description="Description of the medicine"),
     *                 @OA\Property(
     *                     property="variants",
     *                     type="object",
     *                     @OA\Property(property="price", type="number", format="float", description="Selling price"),
     *                     @OA\Property(property="limit_quantity", type="integer", description="Limit quantity"),
     *                     @OA\Property(property="stock_status", type="string", description="Stock status"),
     *                     @OA\Property(property="original_price", type="number", format="float", description="Original price"),
     *                     @OA\Property(property="discount_percent", type="number", format="float", description="Discount percentage"),
     *                     @OA\Property(property="is_featured", type="boolean", description="Is the product featured"),
     *                     @OA\Property(property="is_active", type="boolean", description="Active status")
     *                 ),
     *                 @OA\Property(
     *                     property="details",
     *                     type="object",
     *                     @OA\Property(property="ingredients", type="string", description="Ingredients"),
     *                     @OA\Property(property="usage", type="array", @OA\Items(type="string"), description="Usage instructions"),
     *                     @OA\Property(
     *                         property="paramaters",
     *                         type="object",
     *                         @OA\Property(property="origin", type="string", description="Origin"),
     *                         @OA\Property(property="packaging", type="string", description="Packaging details")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="usageguide",
     *                     type="object",
     *                     @OA\Property(
     *                         property="dosage",
     *                         type="object",
     *                         @OA\Property(property="adult", type="string", description="Dosage for adults"),
     *                         @OA\Property(property="child", type="string", description="Dosage for children")
     *                     ),
     *                     @OA\Property(property="directions", type="array", @OA\Items(type="string"), description="Directions for use"),
     *                     @OA\Property(property="precautions", type="array", @OA\Items(type="string"), description="Warnings and precautions")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Medicine added successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Medicine added successfully"),
     *             @OA\Property(property="status", type="integer", example=201)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Invalid data",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Unable to upload image"),
     *             @OA\Property(property="status", type="integer", example=500)
     *         )
     *     )
     * )
     */
    public function addMedicine(MedicineRequest $request)
    {
        $validated = $request->validated();
        $slug = Str::slug($request->name);
        $data = Medicine::create([
            'category_id' => $validated['category_id'],
            'supplier_id' => $validated['supplier_id'],
            'name' => $validated['name'],
            'slug' => $slug,
            'thumbnail' => [
                'public_id' => null,
                'url' => null,
                'alt' => $slug . '-alt',
            ],
            'description' => $validated['description'],
            'variants' => [
                'price' => $validated['variants']['original_price'] - ($validated['variants']['original_price'] * $validated['variants']['discount_percent'] / 100),
                'limit_quantity' => $validated['variants']['limit_quantity'],
                'stock_status' => $validated['variants']['stock_status'],
                'original_price' => $validated['variants']['original_price'],
                'discount_percent' => $validated['variants']['discount_percent'],
                'is_featured' => $validated['variants']['is_featured'] ? true : false,
                'is_active' => $validated['variants']['is_active'] ? true : false,
            ],
            'ratings' => [
                'star' => 5.0,
                'liked' => 0,
                'review_count' => 0,
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
        if ($data && $data->_id) $this->embeddingService->embedMedicineAsync($data->_id);
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
        if ($request->has('category_id')) $updateData['category_id'] = $request->category_id;
        if ($request->has('supplier_id')) $updateData['supplier_id'] = $request->supplier_id;
        if ($request->has('name')) {
            $updateData['name'] = $request->name;
            $updateData['slug'] = Str::slug($request->name);
        }
        if ($request->has('description')) $updateData['description'] = $request->description;
        if ($request->has('variants')) {
            $currentVariants = $medicine->variants ?? [];
            $newVariants = $request->variants;
            $mergedVariants = array_merge($currentVariants, $newVariants);
            if (isset($newVariants['original_price']) || isset($newVariants['discount_percent'])) {
                $originalPrice = $mergedVariants['original_price'] ?? $currentVariants['original_price'] ?? 0;
                $discountPercent = $mergedVariants['discount_percent'] ?? $currentVariants['discount_percent'] ?? 0;
                $mergedVariants['price'] = $originalPrice - ($originalPrice * $discountPercent / 100);
            }
            $updateData['variants'] = $mergedVariants;
        }
        if ($request->has('details')) {
            $currentDetails = $medicine->details ?? [];
            $newDetails = $request->details;
            $mergedDetails = $currentDetails;
            if (isset($newDetails['ingredients'])) $mergedDetails['ingredients'] = $newDetails['ingredients'];
            if (isset($newDetails['usage'])) $mergedDetails['usage'] = $newDetails['usage'];
            if (isset($newDetails['paramaters'])) {
                $currentParams = $currentDetails['paramaters'] ?? [];
                $mergedDetails['paramaters'] = array_merge($currentParams, $newDetails['paramaters']);
            }
            $updateData['details'] = $mergedDetails;
        }
        if ($request->has('usageguide')) {
            $currentUsageGuide = $medicine->usageguide ?? [];
            $newUsageGuide = $request->usageguide;
            $mergedUsageGuide = $currentUsageGuide;
            if (isset($newUsageGuide['dosage'])) {
                $currentDosage = $currentUsageGuide['dosage'] ?? [];
                $mergedUsageGuide['dosage'] = array_merge($currentDosage, $newUsageGuide['dosage']);
            }
            if (isset($newUsageGuide['directions'])) $mergedUsageGuide['directions'] = $newUsageGuide['directions'];
            if (isset($newUsageGuide['precautions'])) $mergedUsageGuide['precautions'] = $newUsageGuide['precautions'];
            $updateData['usageguide'] = $mergedUsageGuide;
        }
        if ($updateData && isset($updateData['_id'])) $this->embeddingService->embedMedicineAsync($updateData['_id']);
        $medicine->update($updateData);
        $medicine->refresh();
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
        $this->embeddingService->deleteMedicineEmbeddingAsync($id);
        $medicine->delete();
        return $this->json(null, 'Đã xóa thuốc thành công', 200);
    }

    #[Post(uri: '/{id}/upload-image', name: 'admin.medicines.upload-image')]
    /**
     * @OA\Post(
     *     path="/v1/admin/medicines/{id}/upload-image",
     *     summary="Upload medicine image",
     *     description="Upload a thumbnail image for a medicine",
     *     operationId="uploadMedicineImage",
     *     tags={"Medicines"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="id", type="string", description="Medicine ID"),
     *                 @OA\Property(property="thumbnail", type="file", description="Thumbnail image")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Image uploaded successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Image uploaded successfully"),
     *             @OA\Property(property="status", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Invalid data",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Invalid data"),
     *             @OA\Property(property="status", type="integer", example=422)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Unable to upload image"),
     *             @OA\Property(property="status", type="integer", example=500)
     *         )
     *     )
     * )
     */
    public function uploadImage(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'thumbnail' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);
        if ($validator->fails()) return $this->fail(null, 'Dữ liệu không hợp lệ', 422);
        $medicine = Medicine::find($id);
        if (!$medicine) return $this->fail(null, 'Không tìm thấy thuốc', 404);
        $imageUtils = new ImageUtils();
        $uploadResult = $imageUtils->uploadImage(
            $request->file('thumbnail'),
            'medicines'
        );
        if (!$uploadResult['success']) return $this->fail(null, 'Không thể tải ảnh: ' . $uploadResult['message'], 500);
        $medicine->thumbnail = [
            'public_id' => $uploadResult['public_id'],
            'url' => $uploadResult['url'],
            'alt' => $medicine->slug . '-alt',
        ];
        $medicine->save();
        return $this->json($medicine, 'Đã tải ảnh thành công', 200);
    }
}
