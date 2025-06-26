<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\RouteAttributes\Attributes\Delete;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Patch;
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
    #[Get(uri: '/', name: 'admin.supplier.index')]
    /**
     * @OA\Get(
     *     path="/v1/admin/suppliers",
     *     operationId="listSuppliers",
     *     tags={"Supplier"},
     *     summary="Lấy danh sách nhà cung cấp",
     *     description="Trả về danh sách tất cả nhà cung cấp trong hệ thống",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", 
     *                @OA\Items(
     *                    @OA\Property(property="_id", type="string", example="65f1b3fc5bce7125f4001ec2"),
     *                    @OA\Property(property="name", type="string", example="Công ty Dược phẩm XYZ"),
     *                    @OA\Property(property="address", type="string", example="123 Đường ABC, Quận 1, TP.HCM"),
     *                    @OA\Property(property="contact_phone", type="string", example="0912345678"),
     *                    @OA\Property(property="contact_email", type="string", example="contact@xyz-pharma.com"),
     *                    @OA\Property(property="created_at", type="string", format="date-time"),
     *                    @OA\Property(property="updated_at", type="string", format="date-time")
     *                )
     *             ),
     *             @OA\Property(property="message", type="string", example="Danh sách nhà cung cấp"),
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
    public function listSuppliers(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $sortField = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $search = $request->input('s', '');
        $allowedSortFields = ['name', 'address', 'contact_phone', 'contact_email', 'created_at', 'updated_at'];
        if (!in_array($sortField, $allowedSortFields)) $sortField = 'created_at';
        $query = Supplier::query();
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('contact_phone', 'like', "%{$search}%")
                    ->orWhere('contact_email', 'like', "%{$search}%");
            });
        }
        $suppliers = $query->orderBy($sortField, $sortOrder === 'asc' ? 'asc' : 'desc')->paginate($perPage);
        return $this->json($suppliers, 'Danh sách nhà cung cấp');
    }

    #[Get(uri: "/statistics", name: "admin.supplier.statistic", middleware: "role:admin")]
    public function getSupplierStatisticAdmin()
    {
        $totalSuppliers = Supplier::count();
        $data = [
            'total_suppliers' => $totalSuppliers,
        ];
        return $this->json($data, 'Thống kê nhà cung cấp');
    }

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
            'contact_phone' => ['required', 'string', 'min:10', 'max:15', 'regex:/^[\+]?[0-9]+$/', 'unique:suppliers,contact_phone'],
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

    #[Patch(uri: '/update/{id}', name: 'admin.supplier.update')]
    /**
     * @OA\Patch(
     *     path="/v1/admin/suppliers/update/{id}",
     *     operationId="updateSupplier",
     *     tags={"Supplier"},
     *     summary="Cập nhật nhà cung cấp",
     *     description="Cập nhật thông tin nhà cung cấp trong hệ thống",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của nhà cung cấp cần cập nhật",
     *         @OA\Schema(type="string")
     *     ),
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
     *         response=200,
     *         description="Cập nhật thành công",
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
     *             @OA\Property(property="message", type="string", example="Nhà cung cấp đã được cập nhật thành công"),
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="locale", type="string", example="vi_VN"),
     *             @OA\Property(property="error", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Nhà cung cấp không tồn tại",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Nhà cung cấp không tồn tại"),
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
    public function updateSupplier(Request $request, $id)
    {
        $supplier = Supplier::find($id);
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:3|max:255',
            'address' => 'required|string|min:3|max:255',
            'contact_phone' => ['required', 'string', 'min:10', 'max:15', 'regex:/^[\+]?[0-9]+$/', 'unique:suppliers,contact_phone,' . $id],
            'contact_email' => 'required|email|max:255|unique:suppliers,contact_email,' . $id,
        ]);
        if ($validator->fails()) return $this->fail(null, $validator->errors(), 422);
        if (!$supplier) return $this->fail(null, 'Nhà cung cấp không tồn tại', 404);
        $supplier->update([
            'name' => $request->name,
            'address' => $request->address,
            'contact_phone' => $request->contact_phone,
            'contact_email' => $request->contact_email,
        ]);
        return $this->json($supplier, 'Nhà cung cấp đã được cập nhật thành công', 200);
    }

    #[Delete(uri: '/delete/{id}', name: 'admin.supplier.delete')]
    /**
     * @OA\Delete(
     *     path="/v1/admin/suppliers/delete/{id}",
     *     operationId="deleteSupplier",
     *     tags={"Supplier"},
     *     summary="Xóa nhà cung cấp",
     *     description="Xóa một nhà cung cấp trong hệ thống",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của nhà cung cấp cần xóa",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Xóa thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Nhà cung cấp đã được xóa thành công"),
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="locale", type="string", example="vi_VN"),
     *             @OA\Property(property="error", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Nhà cung cấp không tồn tại",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Nhà cung cấp không tồn tại"),
     *             @OA\Property(property="status", type="integer", example=404),
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
    public function deleteSupplier($id)
    {
        $supplier = Supplier::find($id);
        if (!$supplier) return $this->fail(null, 'Nhà cung cấp không tồn tại', 404);
        $supplier->delete();
        return $this->json(null, 'Nhà cung cấp đã được xóa thành công', 200);
    }
}
