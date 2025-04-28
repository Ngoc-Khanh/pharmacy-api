<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;

#[Prefix('v2/medicine')]
class MedicineController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v2/medicine/popular-medicine",
     *     operationId="getPopularMedicine",
     *     summary="Lấy danh sách thuốc phổ biến",
     *     description="Truy xuất danh sách các loại thuốc phổ biến dựa trên số lượt thích",
     *     tags={"Medicines"},
     *     @OA\Response(
     *         response=200,
     *         description="Thao tác thành công",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Medicine")
     *             ),
     *             @OA\Property(property="message", type="string", example="Lấy danh sách thuốc phổ biến thành công"),
     *             @OA\Property(property="status", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi máy chủ",
     *     )
     * )
     */
    #[Get('/popular-medicine', "medicine.popular")]
    public function getPopularMedicine()
    {
        $popularMedicines = Medicine::with(['category', 'supplier'])
            ->where('ratings.liked', '>=', 10)
            ->orderBy('ratings.liked', 'desc')
            ->limit(4)
            ->get()
            ->makeHidden(['category_id', 'supplier_id', 'details', 'usageguide','created_at', 'updated_at']);
        $popularMedicines->each(function ($medicine) {
            if ($medicine->category) $medicine->category->makeHidden(['created_at', 'updated_at']);
            if ($medicine->supplier) $medicine->supplier->makeHidden(['created_at', 'updated_at']);
        });

        return $this->json($popularMedicines, 'Popular medicines fetched successfully', 200);
    }

    /**
     * @OA\Get(
     *     path="/api/v2/medicine/medicine-list",
     *     operationId="getAllMedicine",
     *     summary="Lấy danh sách tất cả các loại thuốc",
     *     description="Truy xuất danh sách tất cả các loại thuốc trong cơ sở dữ liệu",
     *     tags={"Medicines"},
     *     @OA\Response(
     *         response=200,
     *         description="Thao tác thành công",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Medicine")
     *             ),
     *             @OA\Property(property="message", type="string", example="Lấy danh sách thuốc thành công"),
     *             @OA\Property(property="status", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi máy chủ",
     *     )
     * )
     */
    #[Get('/medicine-list', "medicine.list")]
    public function getAllMedicine()
    {
        $medicines = Medicine::with(['category', 'supplier'])->get()->makeHidden(['category_id', 'supplier_id', 'details', 'usageguide','created_at', 'updated_at']);
        $medicines->each(function ($medicine) {
            if ($medicine->category) $medicine->category->makeHidden(['created_at', 'updated_at']);
            if ($medicine->supplier) $medicine->supplier->makeHidden(['created_at', 'updated_at']);
        });
        return $this->json($medicines, 'Medicines fetched successfully', 200);
    }

    /**
     * @OA\Get(
     *     path="/api/v2/medicine/detail/{medicineId}",
     *     operationId="getDetailMedicine",
     *     summary="Lấy chi tiết thuốc",
     *     description="Truy xuất thông tin chi tiết về một loại thuốc cụ thể",
     *     tags={"Medicines"},
     *     @OA\Parameter(
     *         name="medicineId",
     *         in="path",
     *         required=true,
     *         description="ID của thuốc cần truy xuất",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thao tác thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/MedicineDetail"),
     *             @OA\Property(property="message", type="string", example="Lấy thông tin thuốc thành công"),
     *             @OA\Property(property="status", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy thuốc",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Không tìm thấy thuốc"),
     *             @OA\Property(property="status", type="integer", example=404)
     *         )
     *     )
     * )
     */
    #[Get('/detail/{medicineId}', "medicine.detail")]
    public function getDetailMedicine($medicineId)
    {
        $medicine = Medicine::with(['category', 'supplier'])
            ->find($medicineId)
            ?->makeHidden(['category_id', 'supplier_id', 'created_at', 'updated_at']);
        if ($medicine) {
            $medicine->category?->makeHidden(['created_at', 'updated_at']);
            $medicine->supplier?->makeHidden(['created_at', 'updated_at']);
            return $this->json($medicine, 'Medicine fetched successfully', 200);
        }
        return $this->fail([], 'Medicine not found', 404);
    }

    /**
     * @OA\Post(
     *     path="/api/v2/medicine/admin/medicine/add-medicine-step-one",
     *     operationId="addMedicineStepOne",
     *     summary="Thêm thuốc mới (bước một)",
     *     description="Bước đầu tiên trong việc thêm một loại thuốc mới vào cơ sở dữ liệu (chỉ dành cho admin)",
     *     tags={"Medicines"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"category_id", "supplier_id", "name", "priority", "description", "profile_image.image_url"},
     *             @OA\Property(property="category_id", type="string", example="60f1a5b0e5a4d12345678901"),
     *             @OA\Property(property="supplier_id", type="string", example="60f1a5b0e5a4d12345678902"),
     *             @OA\Property(property="name", type="string", example="Paracetamol 500mg"),
     *             @OA\Property(property="priority", type="integer", example=1),
     *             @OA\Property(property="description", type="string", example="Thuốc giảm đau và hạ sốt"),
     *             @OA\Property(
     *                 property="profile_image",
     *                 type="object",
     *                 @OA\Property(property="image_url", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thêm thuốc thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Medicine"),
     *             @OA\Property(property="message", type="string", example="Thêm thuốc thành công"),
     *             @OA\Property(property="status", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Không được phép",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Không được phép"),
     *             @OA\Property(property="status", type="integer", example=401)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Lỗi xác thực",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="object"),
     *             @OA\Property(property="status", type="integer", example=422)
     *         )
     *     )
     * )
     */
    #[Post('/admin/medicine/add-medicine-step-one', "medicine.add")]
    public function addMedicineStepOne(Request $request)
    {
        if ($request->user()->role !== 'admin') return $this->fail([], 'Unauthorized', 401);
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|string|max:255',
            'supplier_id' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'priority' => 'required|integer|min:0',
            'description' => 'required|string',
            'profile_image.image_url' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        if ($validator->fails()) return $this->fail([], $validator->errors(), 422);
        $medicine = Medicine::create([
            'category_id' => $request->category_id,
            'supplier_id' => $request->supplier_id,
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'priority' => $request->priority,
            'description' => $request->description,
        ]);
        return $this->json($medicine, 'Medicine added successfully', 200);
    }
}
