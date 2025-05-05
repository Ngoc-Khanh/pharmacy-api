<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Middleware;

#[Prefix(prefix: 'v1/admin/suppliers')]
#[Middleware(middleware: ['jwt.auth', 'role:admin,pharmacist'])]
/**
 * @OA\Tag(
 *     name="Supplier",
 *     description="Quản lý nhà cung cấp"
 * )
 */
class SupplierController extends Controller
{
    #[Post(uri: '/add', name: 'admin.supplier.add')]
    /**
     * @OA\Post(
     *     path="/v1/admin/suppliers/add",
     *     operationId="addSupplier",
     *     tags={"Supplier"},
     *     summary="Thêm nhà cung cấp mới",
     *     description="Tạo một nhà cung cấp mới trong hệ thống",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "address", "contact_phone", "contact_email"},
     *             @OA\Property(property="name", type="string", example="Công ty Dược phẩm XYZ", description="Tên nhà cung cấp"),
     *             @OA\Property(property="address", type="string", example="123 Đường ABC, Quận 1, TP.HCM", description="Địa chỉ nhà cung cấp"),
     *             @OA\Property(property="contact_phone", type="string", example="0912345678", description="Số điện thoại liên hệ"),
     *             @OA\Property(property="contact_email", type="string", example="contact@xyz-pharma.com", description="Email liên hệ")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tạo thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="_id", type="string", example="65f1b3fc5bce7125f4001ec2"),
     *                 @OA\Property(property="name", type="string", example="Công ty Dược phẩm XYZ"),
     *                 @OA\Property(property="address", type="string", example="123 Đường ABC, Quận 1, TP.HCM"),
     *                 @OA\Property(property="contact_phone", type="string", example="0912345678"),
     *                 @OA\Property(property="contact_email", type="string", example="contact@xyz-pharma.com"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             ),
     *             @OA\Property(property="message", type="string", example="Nhà cung cấp đã được tạo thành công"),
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
     *             @OA\Property(property="message", type="string", example="Vui lòng điền đầy đủ thông tin"),
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
    public function addSupplier(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:3|max:255',
            'address' => 'required|string|min:3|max:255',
            'contact_phone' => 'required|string|min:10|max:15|unique:suppliers,contact_phone',
            'contact_email' => 'required|email|max:255|unique:suppliers,contact_email',
        ]);
        if ($validator->fails()) return $this->fail(null, $validator->errors(), 422);
        $supplier = Supplier::create([
            'name' => $request->name,
            'address' => $request->address,
            'contact_phone' => $request->contact_phone,
            'contact_email' => $request->contact_email,
        ]);
        return $this->json($supplier, 'Nhà cung cấp đã được tạo thành công', 201);
    }
}
