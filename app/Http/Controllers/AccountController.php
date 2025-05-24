<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Medicine;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
    #[Get(uri: "/addresses", name: "account.addresses.index")]
    /**
     * @OA\Get(
     *     path="/v1/store/account/addresses",
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
     *                    @OA\Property(property="address_line1", type="string", example="123 Đường Nguyễn Huệ"),
     *                    @OA\Property(property="address_line2", type="string", example="Phường Bến Nghé"),
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

    #[Post(uri: "/addresses/add", name: "account.addresses.add")]
    /**
     * @OA\Post(
     *     path="/v1/store/account/addresses/add",
     *     operationId="addAddress",
     *     tags={"Account"},
     *     summary="Thêm địa chỉ mới cho người dùng",
     *     description="Thêm một địa chỉ mới vào danh sách địa chỉ của người dùng đã đăng nhập",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "phone", "address_line1", "city", "country", "postal_code"},
     *             @OA\Property(property="name", type="string", example="Nguyễn Văn A", description="Tên người nhận"),
     *             @OA\Property(property="phone", type="string", example="0901234567", description="Số điện thoại"),
     *             @OA\Property(property="address_line1", type="string", example="123 Đường Nguyễn Huệ", description="Địa chỉ dòng 1"),
     *             @OA\Property(property="address_line2", type="string", example="Phường Bến Nghé", description="Địa chỉ dòng 2"),
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
     *                 @OA\Property(property="address_line1", type="string", example="123 Đường Nguyễn Huệ"),
     *                 @OA\Property(property="address_line2", type="string", example="Phường Bến Nghé"),
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
            'address_line1' => 'required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
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
            'address_line1' => $request->address_line1,
            'address_line2' => $request->address_line2 ?? '',
            'city' => $request->city,
            'state' => $request->state ?? '',
            'country' => $request->country,
            'postal_code' => $request->postal_code,
            'is_default' => $request->is_default ?? false,
        ];
        foreach ($addresses as $address) {
            if (
                ($address['address_line1'] ?? '') === $newAddress['address_line1'] &&
                ($address['city'] ?? '') === $newAddress['city'] &&
                ($address['country'] ?? '') === $newAddress['country'] &&
                ($address['postal_code'] ?? '') === $newAddress['postal_code']
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

    #[Patch(uri: "/addresses/update/{id}", name: "account.addresses.update")]
    /**
     * @OA\Patch(
     *     path="/v1/store/account/addresses/update/{id}",
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
     *             @OA\Property(property="address_line1", type="string", example="123 Đường ABC", description="Địa chỉ dòng 1"),
     *             @OA\Property(property="address_line2", type="string", example="Tầng 2", description="Địa chỉ dòng 2"),
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
     *                 @OA\Property(property="address_line1", type="string", example="123 Đường ABC"),
     *                 @OA\Property(property="address_line2", type="string", example="Tầng 2"),
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
            'address_line1',
            'address_line2',
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

    #[Delete(uri: "/addresses/delete/{id}", name: "account.addresses.delete")]
    /**
     * @OA\Delete(
     *     path="/v1/store/account/addresses/delete/{id}",
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

    #[Post("/addresses/set-default-address/{id}", "account.addresses.setDefaultAddress")]
    /**
     * @OA\Post(
     *     path="/v1/store/account/addresses/set-default-address/{id}",
     *     operationId="setDefaultAddress",
     *     tags={"Account"},
     *     summary="Đặt địa chỉ mặc định",
     *     description="Đặt một địa chỉ làm địa chỉ mặc định cho người dùng đã đăng nhập",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của địa chỉ cần đặt làm mặc định",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Đặt địa chỉ mặc định thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="is_default", type="boolean", example=true)
     *             ),
     *             @OA\Property(property="message", type="string", example="Đặt địa chỉ mặc định thành công"),
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
    public function setDefaultAddress(Request $request, string $id)
    {
        $user = $request->user();
        $user->addresses = array_map(function ($address) use ($id) {
            $address['is_default'] = $address['id'] === $id;
            return $address;
        }, $user->addresses);
        $user->save();

        $defaultAddress = collect($user->addresses)->firstWhere('is_default', true);
        return $this->json($defaultAddress, 'Đặt địa chỉ mặc định thành công', 200);
    }

    #[Patch(uri: "/profile/update", name: "account.profile.update")]
    /**
     * @OA\Patch(
     *     path="/v1/store/account/profile/update",
     *     operationId="updateProfile",
     *     tags={"Account"},
     *     summary="Cập nhật thông tin tài khoản",
     *     description="Cập nhật thông tin cá nhân của người dùng đã đăng nhập",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="username", type="string", example="user123"),
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="firstname", type="string", example="Nguyễn"),
     *             @OA\Property(property="lastname", type="string", example="Văn A"),
     *             @OA\Property(property="phone", type="string", example="0901234567")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cập nhật thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="username", type="string", example="user123"),
     *                 @OA\Property(property="email", type="string", example="user@example.com"),
     *                 @OA\Property(property="firstname", type="string", example="Nguyễn"),
     *                 @OA\Property(property="lastname", type="string", example="Văn A"),
     *                 @OA\Property(property="phone", type="string", example="0901234567")
     *             ),
     *             @OA\Property(property="message", type="string", example="Cập nhật thông tin tài khoản thành công"),
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="locale", type="string", example="vi_VN"),
     *             @OA\Property(property="error", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Dữ liệu không hợp lệ",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Dữ liệu không hợp lệ"),
     *             @OA\Property(property="status", type="integer", example=422),
     *             @OA\Property(property="locale", type="string", example="vi_VN"),
     *             @OA\Property(property="error", type="object")
     *         )
     *     )
     * )
     */
    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255',
            'firstname' => 'sometimes|string|max:255',
            'lastname' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20'
        ]);
        if ($validator->fails()) return $this->fail(null, $validator->errors()->first(), 422);
        $user = $request->user();
        $user->fill($request->only([
            'username',
            'email',
            'firstname',
            'lastname',
            'phone'
        ]));
        if (!$user->isDirty()) return $this->json($user, 'Không có thông tin nào được thay đổi', 200);
        $user->save();
        return $this->json($user, 'Cập nhật thông tin tài khoản thành công', 200);
    }

    #[Patch(uri: "/profile/change-password", name: "account.profile.change-password")]
    /**
     * @OA\Patch(
     *     path="/v1/store/account/profile/change-password",
     *     operationId="changePassword",
     *     tags={"Account"},
     *     summary="Cập nhật mật khẩu tài khoản",
     *     description="Cập nhật mật khẩu của người dùng đã đăng nhập",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"current_password", "new_password", "new_password_confirmation"},
     *             @OA\Property(property="current_password", type="string", example="OldPassword123!"),
     *             @OA\Property(property="new_password", type="string", example="NewPassword456@", description="Phải có ít nhất 8 ký tự, bao gồm chữ hoa, chữ thường, số và ký tự đặc biệt"),
     *             @OA\Property(property="new_password_confirmation", type="string", example="NewPassword456@")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cập nhật mật khẩu thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Cập nhật mật khẩu thành công"),
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="locale", type="string", example="vi_VN"),
     *             @OA\Property(property="error", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Mật khẩu hiện tại không chính xác",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Mật khẩu hiện tại không chính xác"),
     *             @OA\Property(property="status", type="integer", example=401),
     *             @OA\Property(property="locale", type="string", example="vi_VN"),
     *             @OA\Property(property="error", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Dữ liệu không hợp lệ",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Mật khẩu mới phải có ít nhất 8 ký tự"),
     *             @OA\Property(property="status", type="integer", example=422),
     *             @OA\Property(property="locale", type="string", example="vi_VN"),
     *             @OA\Property(property="error", type="object")
     *         )
     *     )
     * )
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string|min:8',
            'new_password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[a-z]/',      // Ít nhất một chữ cái thường
                'regex:/[A-Z]/',      // Ít nhất một chữ cái hoa
                'regex:/[0-9]/',      // Ít nhất một số
                'regex:/[@$!%*#?&]/', // Ít nhất một ký tự đặc biệt
            ],
        ]);
        if ($validator->fails()) return $this->fail(null, $validator->errors()->first(), 422);
        $user = $request->user();
        if (!Hash::check($request->current_password, $user->password)) {
            return $this->fail(null, 'Mật khẩu hiện tại không chính xác', 401);
        }
        $user->password = Hash::make($request->new_password);
        $user->save();
        return $this->json(null, 'Cập nhật mật khẩu thành công', 200);
    }

    #[Get(uri: "/carts", name: "account.carts.get")]
    /**
     * @OA\Get(
     *     path="/v1/store/account/carts",
     *     operationId="getUserCart",
     *     tags={"Account"},
     *     summary="Lấy giỏ hàng của người dùng",
     *     description="Trả về thông tin giỏ hàng của người dùng đã đăng nhập bao gồm danh sách sản phẩm và tổng giá trị",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lấy giỏ hàng thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="_id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="user_id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="items", type="array", 
     *                     @OA\Items(
     *                         @OA\Property(property="medicine_id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                         @OA\Property(property="quantity", type="integer", example=2),
     *                         @OA\Property(property="medicine", type="object",
     *                             @OA\Property(property="id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                             @OA\Property(property="name", type="string", example="Paracetamol"),
     *                             @OA\Property(property="slug", type="string", example="paracetamol"),
     *                             @OA\Property(property="thumbnail", type="object",
     *                                 @OA\Property(property="url", type="string", example="https://example.com/images/paracetamol.jpg"),
     *                                 @OA\Property(property="alt", type="string", example="paracetamol-alt")
     *                             ),
     *                             @OA\Property(property="price", type="number", example=15000),
     *                             @OA\Property(property="stock_status", type="string", example="in_stock")
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(property="total", type="number", example=30000),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2023-06-15T14:30:00Z")
     *             ),
     *             @OA\Property(property="message", type="string", example="Lấy giỏ hàng thành công"),
     *             @OA\Property(property="status", type="integer", example=200)
     *         )
     *     )
     * )
     */
    public function getUserCart(Request $request)
    {
        $user = $request->user();
        $cart = Cart::where('user_id', $user->_id ?? $user->id)->first();
        if (!$cart || empty($cart->items)) {
            if ($cart) $cart->total = 0;
            return $this->json($cart, 'Lấy giỏ hàng thành công', 200);
        }

        $medicineIds = collect($cart->items)->pluck('medicine_id')->toArray();
        $medicines = Medicine::whereIn('_id', $medicineIds)->get()->keyBy('_id');
        $total = 0;

        $cart->items = collect($cart->items)
            ->map(function ($item) use ($medicines, &$total) {
                if (!isset($medicines[$item['medicine_id']])) return null;
                $medicine = $medicines[$item['medicine_id']];
                $price = $medicine->variants['price'] ?? 0;
                $total += $price * $item['quantity'];
                $item['medicine'] = $medicine; // Gán toàn bộ object medicine
                return $item;
            })->filter()->values()->toArray();

        $cart->total = $total;
        return $this->json($cart, 'Lấy giỏ hàng thành công', 200);
    }

    #[Post(uri: "/carts/add", name: "account.carts.add")]
    /**
     * @OA\Post(
     *     path="/v1/store/account/carts/add",
     *     summary="Thêm sản phẩm vào giỏ hàng",
     *     description="Thêm một sản phẩm thuốc vào giỏ hàng của người dùng",
     *     operationId="addToCart",
     *     tags={"Account"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"medicine_id", "quantity"},
     *             @OA\Property(property="medicine_id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *             @OA\Property(property="quantity", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thêm vào giỏ hàng thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Đã thêm sản phẩm vào giỏ hàng"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="_id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="user_id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="items", type="array", 
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="medicine_id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                         @OA\Property(property="quantity", type="integer", example=1)
     *                     )
     *                 ),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2023-01-01T00:00:00.000000Z"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2023-01-01T00:00:00.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Dữ liệu không hợp lệ",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Dữ liệu không hợp lệ"),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="error", type="object")
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
    public function addToCart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'medicine_id' => 'required|string|exists:medicines,_id',
            'quantity' => 'required|numeric|min:1',
        ]);
        if ($validator->fails()) return $this->fail(null, $validator->errors()->first(), 422);
        $userId = $request->user()->_id;
        $medicineId = $request->medicine_id;
        $quantity = (int)$request->quantity; // Convert to integer
        $cart = Cart::where('user_id', $userId)->first();
        if (!$cart) {
            $cart = new Cart();
            $cart->user_id = $userId;
            $cart->items = [[
                'medicine_id' => $medicineId,
                'quantity' => $quantity,
            ]];
            $cart->save();
        } else {
            $items = $cart->items ?? [];
            $found = false;
            $items = collect($items)->map(function ($item) use ($medicineId, $quantity, &$found) {
                if ($item['medicine_id'] == $medicineId) {
                    $found = true;
                    $item['quantity'] = (int)$item['quantity'] + $quantity;
                }
                return $item;
            })->toArray();
            if (!$found) {
                $items[] = [
                    'medicine_id' => $medicineId,
                    'quantity' => $quantity,
                ];
            }
            $cart->items = $items;
            $cart->save();
        }
        return $this->json($cart, 'Đã thêm sản phẩm vào giỏ hàng', 200);
    }

    #[Delete(uri: "/carts/remove/{id}", name: "account.carts.remove")]
    /**
     * @OA\Delete(
     *     path="/v1/store/account/carts/remove/{id}",
     *     operationId="removeFromCart",
     *     tags={"Account"},
     *     summary="Xóa sản phẩm khỏi giỏ hàng",
     *     description="Xóa một sản phẩm thuốc khỏi giỏ hàng của người dùng",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của thuốc cần xóa khỏi giỏ hàng",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Xóa sản phẩm thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Đã xóa sản phẩm khỏi giỏ hàng"),
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="locale", type="string", example="vi_VN"),
     *             @OA\Property(property="error", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy giỏ hàng hoặc sản phẩm",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Giỏ hàng không tồn tại"),
     *             @OA\Property(property="status", type="integer", example=404),
     *             @OA\Property(property="locale", type="string", example="vi_VN"),
     *             @OA\Property(property="error", type="null")
     *         )
     *     ),
     * )
     */
    public function removeFromCart(Request $request, $id)
    {
        $validator = Validator::make(['medicine_id' => $id], [
            'medicine_id' => 'required|string|exists:medicines,_id',
        ]);
        if ($validator->fails()) return $this->fail(null, $validator->errors()->first(), 422);
        $userId = $request->user()->_id;
        $medicineId = $id;
        $cart = Cart::where('user_id', $userId)->first();
        if (!$cart) return $this->fail(null, 'Giỏ hàng không tồn tại', 404);
        $items = collect($cart->items ?? []);
        if ($items->isEmpty()) return $this->fail(null, 'Giỏ hàng trống', 404);
        $items = $items->filter(function ($item) use ($medicineId) {
            return $item['medicine_id'] !== $medicineId;
        })->values()->toArray();
        if (empty($items)) {
            Cart::where('user_id', $userId)->delete();
            return $this->json(null, 'Đã xóa sản phẩm khỏi giỏ hàng và giỏ hàng đã được xóa', 200);
        }
        $cart->items = $items;
        $cart->save();
        return $this->json($cart, 'Đã xóa sản phẩm khỏi giỏ hàng', 200);
    }
}
