<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\RouteAttributes\Attributes\Delete;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Patch;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Middleware;

#[Prefix(prefix: 'v1/store/account')]
#[Middleware(middleware: ['jwt.auth'])]
/**
 * @OA\Tag(
 *     name="Account",
 *     description="Quản lý tài khoản của người dùng"
 * )
 */
class AccountController extends Controller
{
    #[Get(uri: "/address", name: "account.addresses.index")]
    /**
     * @OA\Get(
     *     path="/v1/store/account/address",
     *     operationId="getAddressList",
     *     tags={"Account"},
     *     summary="Lấy danh sách địa chỉ của người dùng",
     *     description="Trả về danh sách tất cả địa chỉ của người dùng đã đăng nhập",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", 
     *                @OA\Items(
     *                    @OA\Property(property="id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                    @OA\Property(property="name", type="string", example="Nguyễn Văn A"),
     *                    @OA\Property(property="phone", type="string", example="0901234567"),
     *                    @OA\Property(property="address_line_1", type="string", example="123 Đường Nguyễn Huệ"),
     *                    @OA\Property(property="address_line_2", type="string", example="Phường Bến Nghé"),
     *                    @OA\Property(property="city", type="string", example="Quận 1"),
     *                    @OA\Property(property="state", type="string", example="TP Hồ Chí Minh"),
     *                    @OA\Property(property="country", type="string", example="Việt Nam"),
     *                    @OA\Property(property="postal_code", type="string", example="700000"),
     *                    @OA\Property(property="is_default", type="boolean", example=true)
     *                )
     *             ),
     *             @OA\Property(property="message", type="string", example="Danh sách địa chỉ"),
     *             @OA\Property(property="status", type="integer", example=200)
     *         )
     *     )
     * )
     */
    public function addressesList()
    {
        $user = request()->user();
        $addresses = $user->addresses ?? [];
        return $this->json($addresses, 'Danh sách địa chỉ', 200);
    }

    #[Post(uri: "/address/add", name: "account.addresses.add")]
    /**
     * @OA\Post(
     *     path="/v1/store/account/address/add",
     *     operationId="addAddress",
     *     tags={"Account"},
     *     summary="Thêm địa chỉ mới cho người dùng",
     *     description="Thêm một địa chỉ mới vào danh sách địa chỉ của người dùng đã đăng nhập",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "phone", "address_line_1", "city", "country", "postal_code"},
     *             @OA\Property(property="name", type="string", example="Nguyễn Văn A", description="Tên người nhận"),
     *             @OA\Property(property="phone", type="string", example="0901234567", description="Số điện thoại"),
     *             @OA\Property(property="address_line_1", type="string", example="123 Đường Nguyễn Huệ", description="Địa chỉ dòng 1"),
     *             @OA\Property(property="address_line_2", type="string", example="Phường Bến Nghé", description="Địa chỉ dòng 2"),
     *             @OA\Property(property="city", type="string", example="Quận 1", description="Quận/Huyện"),
     *             @OA\Property(property="state", type="string", example="TP Hồ Chí Minh", description="Tỉnh/Thành phố"),
     *             @OA\Property(property="country", type="string", example="Việt Nam", description="Quốc gia"),
     *             @OA\Property(property="postal_code", type="string", example="700000", description="Mã bưu điện"),
     *             @OA\Property(property="is_default", type="boolean", example=false, description="Đặt làm địa chỉ mặc định")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Địa chỉ đã được thêm thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="name", type="string", example="Nguyễn Văn A"),
     *                 @OA\Property(property="phone", type="string", example="0901234567"),
     *                 @OA\Property(property="address_line_1", type="string", example="123 Đường Nguyễn Huệ"),
     *                 @OA\Property(property="address_line_2", type="string", example="Phường Bến Nghé"),
     *                 @OA\Property(property="city", type="string", example="Quận 1"),
     *                 @OA\Property(property="state", type="string", example="TP Hồ Chí Minh"),
     *                 @OA\Property(property="country", type="string", example="Việt Nam"),
     *                 @OA\Property(property="postal_code", type="string", example="700000"),
     *                 @OA\Property(property="is_default", type="boolean", example=true)
     *             ),
     *             @OA\Property(property="message", type="string", example="Địa chỉ đã được thêm thành công"),
     *             @OA\Property(property="status", type="integer", example=201),
     *             @OA\Property(property="locale", type="string", example="vi_VN"),
     *             @OA\Property(property="error", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Địa chỉ đã tồn tại",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items()),
     *             @OA\Property(property="message", type="string", example="Địa chỉ đã tồn tại"),
     *             @OA\Property(property="status", type="integer", example=400),
     *             @OA\Property(property="locale", type="string", example="vi_VN"),
     *             @OA\Property(property="error", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Dữ liệu không hợp lệ",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="status", type="integer", example=422),
     *             @OA\Property(property="locale", type="string", example="vi_VN"),
     *             @OA\Property(property="error", type="object")
     *         )
     *     )
     * )
     */
    public function addAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'is_default' => 'nullable|boolean',
        ]);
        if ($validator->fails()) return $this->fail($validator->errors(), 'Validation failed', 422);
        $user = $request->user();
        $addresses = $user->addresses ?? [];
        $newAddress = [
            'id' => Str::uuid()->toString(),
            'name' => $request->name,
            'phone' => $request->phone,
            'address_line_1' => $request->address_line_1,
            'address_line_2' => $request->address_line_2 ?? '',
            'city' => $request->city,
            'state' => $request->state ?? '',
            'country' => $request->country,
            'postal_code' => $request->postal_code,
            'is_default' => $request->is_default ?? false,
        ];
        foreach ($addresses as $address) {
            if (
                ($address['address_line_1'] ?? $address['address_line1']) === $newAddress['address_line_1'] &&
                $address['city'] === $newAddress['city'] &&
                $address['country'] === $newAddress['country'] &&
                $address['postal_code'] === $newAddress['postal_code']
            ) {
                return $this->fail([], 'Địa chỉ đã tồn tại', 400);
            }
        }
        $isDefault = $request->is_default ?? false;
        if (empty($addresses) || $isDefault) {
            $newAddress['is_default'] = true;
            if (!empty($addresses)) {
                foreach ($addresses as &$address) {
                    $address['is_default'] = false;
                }
            }
        }
        $addresses[] = $newAddress;
        $user->addresses = $addresses;
        $user->save();
        return $this->json($newAddress, 'Địa chỉ đã được thêm thành công', 201);
    }

    #[Patch(uri: "/address/update/{id}", name: "account.addresses.update")]
    /**
     * @OA\Patch(
     *     path="/v1/store/account/address/update/{id}",
     *     operationId="updateUserAddress",
     *     tags={"Account"},
     *     summary="Cập nhật địa chỉ của người dùng",
     *     description="Cập nhật thông tin địa chỉ đã tồn tại của người dùng",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của địa chỉ cần cập nhật",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Nguyễn Văn A", description="Tên người nhận"),
     *             @OA\Property(property="phone", type="string", example="0901234567", description="Số điện thoại"),
     *             @OA\Property(property="address_line_1", type="string", example="123 Đường ABC", description="Địa chỉ dòng 1"),
     *             @OA\Property(property="address_line_2", type="string", example="Tầng 2", description="Địa chỉ dòng 2"),
     *             @OA\Property(property="city", type="string", example="Hồ Chí Minh", description="Thành phố"),
     *             @OA\Property(property="state", type="string", example="", description="Tỉnh/Bang"),
     *             @OA\Property(property="country", type="string", example="Việt Nam", description="Quốc gia"),
     *             @OA\Property(property="postal_code", type="string", example="700000", description="Mã bưu điện"),
     *             @OA\Property(property="is_default", type="boolean", example=true, description="Đặt làm địa chỉ mặc định")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cập nhật địa chỉ thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="name", type="string", example="Nguyễn Văn A"),
     *                 @OA\Property(property="phone", type="string", example="0901234567"),
     *                 @OA\Property(property="address_line_1", type="string", example="123 Đường ABC"),
     *                 @OA\Property(property="address_line_2", type="string", example="Tầng 2"),
     *                 @OA\Property(property="city", type="string", example="Hồ Chí Minh"),
     *                 @OA\Property(property="state", type="string", example=""),
     *                 @OA\Property(property="country", type="string", example="Việt Nam"),
     *                 @OA\Property(property="postal_code", type="string", example="700000"),
     *                 @OA\Property(property="is_default", type="boolean", example=true)
     *             ),
     *             @OA\Property(property="message", type="string", example="Cập nhật địa chỉ thành công"),
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="locale", type="string", example="vi_VN"),
     *             @OA\Property(property="error", type="null", example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy địa chỉ",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null", example=null),
     *             @OA\Property(property="message", type="string", example="Không tìm thấy địa chỉ"),
     *             @OA\Property(property="status", type="integer", example=404),
     *             @OA\Property(property="locale", type="string", example="vi_VN"),
     *             @OA\Property(property="error", type="array", @OA\Items(type="string"), example={})
     *         )
     *     )
     * )
     */
    public function updateAddress(Request $request, $id)
    {
        $user = $request->user();
        $addresses = $user->addresses ?? [];
        $addressIndex = null;
        $updatedAddress = null;
        // Tìm địa chỉ cần cập nhật
        foreach ($addresses as $index => $address) {
            if ($address['id'] === $id) {
                $addressIndex = $index;
                $updatedAddress = $address;
                break;
            }
        }
        if ($updatedAddress === null) return $this->fail([], 'Không tìm thấy địa chỉ', 404);
        $fieldsToUpdate = [
            'name',
            'phone',
            'address_line_1',
            'address_line_2',
            'city',
            'state',
            'country',
            'postal_code'
        ];
        foreach ($fieldsToUpdate as $field) {
            if ($request->has($field)) $addresses[$addressIndex][$field] = $request->$field;
        }
        // Xử lý địa chỉ mặc định
        if ($request->has('is_default') && $request->is_default) {
            foreach ($addresses as &$addr) {
                $addr['is_default'] = false;
            }
            $addresses[$addressIndex]['is_default'] = true;
        }
        $user->addresses = $addresses;
        $user->save();
        return $this->json($addresses[$addressIndex], 'Cập nhật địa chỉ thành công', 200);
    }

    #[Delete(uri: "/address/delete/{id}", name: "account.addresses.delete")]
    /**
     * @OA\Delete(
     *     path="/v1/store/account/address/delete/{id}",
     *     operationId="deleteAddress",
     *     tags={"Account"},
     *     summary="Xóa địa chỉ của người dùng",
     *     description="Xóa một địa chỉ từ danh sách địa chỉ của người dùng đã đăng nhập",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của địa chỉ cần xóa",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Địa chỉ đã được xóa thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(), example={}),
     *             @OA\Property(property="message", type="string", example="Địa chỉ đã được xóa thành công"),
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="locale", type="string", example="vi_VN"),
     *             @OA\Property(property="error", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy địa chỉ",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Không tìm thấy địa chỉ"),
     *             @OA\Property(property="status", type="integer", example=404),
     *             @OA\Property(property="locale", type="string", example="vi_VN"),
     *             @OA\Property(property="error", type="null")
     *         )
     *     )
     * )
     */
    public function deleteAddress($id)
    {
        $user = request()->user();
        $addresses = $user->addresses ?? [];
        $addresses = array_filter($addresses, function ($address) use ($id) {
            return $address['id'] !== $id;
        });
        $user->addresses = $addresses;
        $user->save();
        return $this->json([], 'Địa chỉ đã được xóa thành công', 200);
    }
}
