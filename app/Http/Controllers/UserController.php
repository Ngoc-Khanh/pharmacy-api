<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AddAddressRequest;
use App\Models\User;
use Cloudinary\Cloudinary;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use MongoDB\BSON\ObjectId;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Patch;
use Spatie\RouteAttributes\Attributes\Delete;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Middleware;

#[Prefix("v2/users")]
#[Middleware("jwt.auth")]
class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/v2/users/admin/users-list",
     *     summary="Lấy danh sách tất cả người dùng",
     *     description="Truy xuất danh sách tất cả người dùng. Yêu cầu quyền admin.",
     *     operationId="getAllUsers",
     *     tags={"Admin"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lấy danh sách người dùng thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lấy danh sách người dùng thành công"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/User"))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Không được phép")
     * )
     */
    #[Get("/admin/users-list", "users.adminUsersList")]
    public function getAllUsers(Request $request)
    {
        $user = $request->user();
        if (!$user->role === 'admin') return $this->fail([], 'Unauthorized', 401);
        $users = User::all();
        return $this->json($users, 'Users retrieved successfully', 200);
    }

    /**
     * @OA\Post(
     *     path="/v2/users/admin/add-users",
     *     summary="Thêm người dùng mới",
     *     description="Tạo một người dùng mới. Yêu cầu quyền admin hoặc user.",
     *     operationId="addUsers",
     *     tags={"Admin"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username", "email", "password", "password_confirmation", "first_name", "last_name", "phone", "role", "status"},
     *             @OA\Property(property="username", type="string", example="johndoe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123"),
     *             @OA\Property(property="first_name", type="string", example="John"),
     *             @OA\Property(property="last_name", type="string", example="Doe"),
     *             @OA\Property(property="phone", type="string", example="+84123456789"),
     *             @OA\Property(property="role", type="string", enum={"admin", "pharmacist", "customer"}, example="customer"),
     *             @OA\Property(property="status", type="string", enum={"active", "inactive", "banned"}, example="active")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tạo người dùng thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tạo người dùng thành công"),
     *             @OA\Property(property="data", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Không được phép"),
     *     @OA\Response(response=422, description="Lỗi xác thực")
     * )
     */
    #[Post("/admin/add-users", "users.adminAddUsers")]
    public function addUsers(Request $request)
    {
        $user = $request->user();
        if (!in_array($user->role, ['admin', 'user'])) return $this->fail([], 'Unauthorized', 401);
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|confirmed|min:8',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'role' => 'required|string|in:admin,pharmacist,customer',
            'status' => 'required|string|in:active,inactive,banned',
        ]);
        if ($validator->fails()) return $this->fail([], $validator->errors(), 422);
        $newUser = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'profile_image' => null,
            'role' => $request->role,
            'status' => $request->status,
            'addresses' => [],
        ]);
        return $this->json($newUser, 'User created successfully', 201);
    }

    /**
     * @OA\Patch(
     *     path="/v2/users/admin/update-user/{id}",
     *     summary="Cập nhật thông tin người dùng",
     *     description="Cập nhật thông tin người dùng. Yêu cầu quyền admin.",
     *     operationId="updateUser",
     *     tags={"Admin"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của người dùng",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="first_name", type="string", example="John"),
     *             @OA\Property(property="last_name", type="string", example="Doe"),
     *             @OA\Property(property="username", type="string", example="johndoe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="newpassword123"),
     *             @OA\Property(property="phone", type="string", example="+84123456789"),
     *             @OA\Property(property="role", type="string", enum={"admin", "pharmacist", "customer"}, example="customer"),
     *             @OA\Property(property="status", type="string", enum={"active", "inactive", "banned"}, example="active")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cập nhật người dùng thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Cập nhật người dùng thành công"),
     *             @OA\Property(property="data", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Không được phép"),
     *     @OA\Response(response=404, description="Không tìm thấy người dùng")
     * )
     */
    #[Patch("/admin/update-user/{id}", "users.adminUpdateUser")]
    public function updateUser(Request $request, string $id)
    {
        $user = $request->user();
        if (!$user->role === 'admin') return $this->fail([], 'Unauthorized', 401);
        $user = User::find($id);
        if (!$user) return $this->fail([], 'User not found', 404);
        $user->fill($request->only([
            'first_name',
            'last_name',
            'username',
            'email',
            'password',
            'phone',
            'role',
            'status',
        ]));
        if ($request->has('password')) $user->password = Hash::make($request->password);
        $user->save();
        return $this->json($user, 'User updated successfully', 200);
    }

    /**
     * @OA\Delete(
     *     path="/v2/users/admin/delete-users/{id}",
     *     summary="Xóa người dùng",
     *     description="Xóa một người dùng. Yêu cầu quyền admin.",
     *     operationId="deleteUsers",
     *     tags={"Admin"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của người dùng",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=204, description="Xóa người dùng thành công"),
     *     @OA\Response(response=401, description="Không được phép"),
     *     @OA\Response(response=404, description="Không tìm thấy người dùng")
     * )
     */
    #[Delete("/admin/delete-users/{id}", "users.adminDeleteUsers")]
    public function deleteUsers(Request $request, string $id)
    {
        $user = $request->user();
        if (!$user->role === 'admin') return $this->fail([], 'Unauthorized', 401);
        $user = User::find($id);
        if (!$user) return $this->fail([], 'User not found', 404);
        $user->delete();
        return $this->json([], 'User deleted successfully', 204);
    }

    /**
     * @OA\Post(
     *     path="/v2/users/admin/change-user-status/{id}",
     *     summary="Thay đổi trạng thái người dùng",
     *     description="Thay đổi trạng thái của một người dùng. Yêu cầu quyền admin.",
     *     operationId="changeUserStatus",
     *     tags={"Admin"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của người dùng",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"active", "inactive", "banned"}, example="inactive")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thay đổi trạng thái người dùng thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Thay đổi trạng thái người dùng thành công"),
     *             @OA\Property(property="data", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Không được phép"),
     *     @OA\Response(response=404, description="Không tìm thấy người dùng")
     * )
     */
    #[Post("/admin/change-user-status/{id}", "users.adminChangeUserStatus")]
    public function changeUserStatus(Request $request, string $id)
    {
        $user = $request->user();
        if (!$user->role === 'admin') return $this->fail([], 'Unauthorized', 401);
        $user = User::find($id);
        if (!$user) return $this->fail([], 'User not found', 404);
        $user->status = $request->status;
        $user->save();
        return $this->json($user, 'User status changed successfully', 200);
    }

    /**
     * @OA\Patch(
     *     path="/v2/users/update-profile",
     *     summary="Cập nhật hồ sơ người dùng",
     *     description="Cập nhật hồ sơ của người dùng đã xác thực.",
     *     operationId="updateProfile",
     *     tags={"User Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="first_name", type="string", example="John"),
     *             @OA\Property(property="last_name", type="string", example="Doe"),
     *             @OA\Property(property="username", type="string", example="johndoe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="phone", type="string", example="+84123456789")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Profile updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    #[Patch("/update-profile", "users.updateProfile")]
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        /** @var \App\Models\User $user */
        $user->fill($request->only([
            'first_name',
            'last_name',
            'username',
            'email',
            'phone'
        ]));
        $user->save();
        return $this->json($user, 'Profile updated successfully', 200);
    }

    /**
     * @OA\Get(
     *     path="/v2/users/addresses",
     *     summary="Get user addresses",
     *     description="Retrieves all addresses of the authenticated user.",
     *     operationId="getAddresses",
     *     tags={"User Addresses"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Addresses retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Addresses retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="string", example="507f1f77bcf86cd799439011"),
     *                     @OA\Property(property="name", type="string", example="Home"),
     *                     @OA\Property(property="phone", type="string", example="+84123456789"),
     *                     @OA\Property(property="address_line1", type="string", example="123 Main St"),
     *                     @OA\Property(property="address_line2", type="string", example="Apt 4B"),
     *                     @OA\Property(property="city", type="string", example="Hanoi"),
     *                     @OA\Property(property="state", type="string", example=""),
     *                     @OA\Property(property="country", type="string", example="Vietnam"),
     *                     @OA\Property(property="postal_code", type="string", example="100000"),
     *                     @OA\Property(property="is_default", type="boolean", example=true),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    #[Get("/addresses", "users.addresses")]
    public function getAddresses(Request $request)
    {
        $user = $request->user();
        return $this->json($user->addresses, 'Addresses retrieved successfully', 200);
    }

    /**
     * @OA\Post(
     *     path="/v2/users/add-addresses",
     *     summary="Add user address",
     *     description="Adds a new address for the authenticated user.",
     *     operationId="addAddress",
     *     tags={"User Addresses"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "phone", "address_line1", "city", "country", "postal_code"},
     *             @OA\Property(property="name", type="string", example="Home"),
     *             @OA\Property(property="phone", type="string", example="+84123456789"),
     *             @OA\Property(property="address_line1", type="string", example="123 Main St"),
     *             @OA\Property(property="address_line2", type="string", example="Apt 4B"),
     *             @OA\Property(property="city", type="string", example="Hanoi"),
     *             @OA\Property(property="state", type="string", example=""),
     *             @OA\Property(property="country", type="string", example="Vietnam"),
     *             @OA\Property(property="postal_code", type="string", example="100000"),
     *             @OA\Property(property="is_default", type="boolean", example=false)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Address added successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Address added successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", example="507f1f77bcf86cd799439011"),
     *                 @OA\Property(property="name", type="string", example="Home"),
     *                 @OA\Property(property="phone", type="string", example="+84123456789"),
     *                 @OA\Property(property="address_line1", type="string", example="123 Main St"),
     *                 @OA\Property(property="address_line2", type="string", example="Apt 4B"),
     *                 @OA\Property(property="city", type="string", example="Hanoi"),
     *                 @OA\Property(property="state", type="string", example=""),
     *                 @OA\Property(property="country", type="string", example="Vietnam"),
     *                 @OA\Property(property="postal_code", type="string", example="100000"),
     *                 @OA\Property(property="is_default", type="boolean", example=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Address already exists"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    #[Post("/add-addresses", "users.addAddresses")]
    public function addAddress(AddAddressRequest $request)
    {
        $user = $request->user();
        $newAddress = [
            'id'            => (string) new ObjectId(),
            'name'          => $request->name,
            'phone'         => $request->phone,
            'address_line1' => $request->address_line1,
            'address_line2' => $request->address_line2 ?? '',
            'city'          => $request->city,
            'state'         => $request->state ?? '',
            'country'       => $request->country,
            'postal_code'   => $request->postal_code,
            'is_default'    => $request->is_default ?? false,
            'created_at'    => now(),
            'updated_at'    => now(),
        ];
        $addresses = $user->addresses ?? [];
        if (!empty($addresses)) {
            foreach ($addresses as $address) {
                if (
                    $address['address_line1'] === $newAddress['address_line1'] &&
                    $address['city'] === $newAddress['city'] &&
                    $address['country'] === $newAddress['country'] &&
                    $address['postal_code'] === $newAddress['postal_code']
                ) {
                    return $this->fail([], 'Address already exists', 400);
                }
            }
        }
        if (empty($addresses)) {
            $newAddress['is_default'] = true;
        } elseif ($newAddress['is_default'] || ($request->has('is_default') && $request->is_default)) {
            $newAddress['is_default'] = true;
            $addresses = array_map(function ($address) {
                $address['is_default'] = false;
                return $address;
            }, $addresses);
        }
        $addresses[] = $newAddress;
        $user->addresses = $addresses;
        $user->save();

        return $this->json($newAddress, 'Address added successfully', 201);
    }

    /**
     * @OA\Patch(
     *     path="/v2/users/update-address/{id}",
     *     summary="Update user address",
     *     description="Updates an address for the authenticated user.",
     *     operationId="updateAddress",
     *     tags={"User Addresses"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Address ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Home"),
     *             @OA\Property(property="phone", type="string", example="+84123456789"),
     *             @OA\Property(property="address_line1", type="string", example="123 Main St"),
     *             @OA\Property(property="address_line2", type="string", example="Apt 4B"),
     *             @OA\Property(property="city", type="string", example="Hanoi"),
     *             @OA\Property(property="state", type="string", example=""),
     *             @OA\Property(property="country", type="string", example="Vietnam"),
     *             @OA\Property(property="postal_code", type="string", example="100000"),
     *             @OA\Property(property="is_default", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Address updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Address updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", example="507f1f77bcf86cd799439011"),
     *                 @OA\Property(property="name", type="string", example="Home"),
     *                 @OA\Property(property="phone", type="string", example="+84123456789"),
     *                 @OA\Property(property="address_line1", type="string", example="123 Main St"),
     *                 @OA\Property(property="address_line2", type="string", example="Apt 4B"),
     *                 @OA\Property(property="city", type="string", example="Hanoi"),
     *                 @OA\Property(property="state", type="string", example=""),
     *                 @OA\Property(property="country", type="string", example="Vietnam"),
     *                 @OA\Property(property="postal_code", type="string", example="100000"),
     *                 @OA\Property(property="is_default", type="boolean", example=true),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Address not found")
     * )
     */
    #[Patch("/update-address/{id}", "users.updateAddress")]
    public function updateAddress(Request $request, string $id)
    {
        $user = $request->user();
        $addresses = $user->addresses ?? [];
        $updatedAddress = null;
        foreach ($addresses as &$address) {
            if ($address['id'] === $id) {
                $address['name'] = $request->name ?? $address['name'];
                $address['phone'] = $request->phone ?? $address['phone'];
                $address['address_line1'] = $request->address_line1 ?? $address['address_line1'];
                $address['address_line2'] = $request->address_line2 ?? $address['address_line2'];
                $address['city'] = $request->city ?? $address['city'];
                $address['state'] = $request->state ?? $address['state'];
                $address['country'] = $request->country ?? $address['country'];
                $address['postal_code'] = $request->postal_code ?? $address['postal_code'];
                $address['updated_at'] = now();
                if ($request->has('is_default') && $request->is_default) {
                    foreach ($addresses as &$addr) {
                        $addr['is_default'] = false;
                    }
                    $address['is_default'] = true;
                }
                $updatedAddress = $address;
                break;
            }
        }
        if (!$updatedAddress) return $this->fail([], 'Address not found', 404);
        $user->addresses = $addresses;
        $user->save();
        return $this->json($updatedAddress, 'Address updated successfully', 200);
    }

    /**
     * @OA\Post(
     *     path="/v2/users/set-default-address/{id}",
     *     summary="Set default address",
     *     description="Sets an address as the default address for the authenticated user.",
     *     operationId="setDefaultAddress",
     *     tags={"User Addresses"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Address ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Default address set successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Default address set successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", example="507f1f77bcf86cd799439011"),
     *                 @OA\Property(property="name", type="string", example="Home"),
     *                 @OA\Property(property="phone", type="string", example="+84123456789"),
     *                 @OA\Property(property="address_line1", type="string", example="123 Main St"),
     *                 @OA\Property(property="address_line2", type="string", example="Apt 4B"),
     *                 @OA\Property(property="city", type="string", example="Hanoi"),
     *                 @OA\Property(property="state", type="string", example=""),
     *                 @OA\Property(property="country", type="string", example="Vietnam"),
     *                 @OA\Property(property="postal_code", type="string", example="100000"),
     *                 @OA\Property(property="is_default", type="boolean", example=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    #[Post("/set-default-address/{id}", "users.setDefaultAddress")]
    public function setDefaultAddress(Request $request, string $id)
    {
        $user = $request->user();
        $user->addresses = array_map(function ($address) use ($id) {
            $address['is_default'] = $address['id'] === $id;
            return $address;
        }, $user->addresses);
        $user->save();

        $defaultAddress = collect($user->addresses)->firstWhere('is_default', true);
        return $this->json($defaultAddress, 'Default address set successfully', 200);
    }

    /**
     * @OA\Delete(
     *     path="/v2/users/delete-address/{id}",
     *     summary="Delete address",
     *     description="Deletes an address for the authenticated user.",
     *     operationId="deleteAddress",
     *     tags={"User Addresses"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Address ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=204, description="Address deleted successfully"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    #[Delete("/delete-address/{id}", "users.deleteAddress")]
    public function deleteAddress(Request $request, string $id)
    {
        $user = $request->user();
        $user->addresses = array_filter($user->addresses, function ($address) use ($id) {
            return $address['id'] !== $id;
        });
        $user->save();
        return $this->json([], 'Address deleted successfully', 204);
    }

    /**
     * @OA\Post(
     *     path="/v2/users/upload-image-profile",
     *     summary="Upload profile image",
     *     description="Uploads a profile image for the authenticated user.",
     *     operationId="uploadImageProfile",
     *     tags={"User Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"profile_image"},
     *                 @OA\Property(
     *                     property="profile_image",
     *                     type="string",
     *                     format="binary",
     *                     description="The profile image file (jpg, jpeg, png, max: 5MB)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile image uploaded successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Profile image uploaded successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="profile_image",
     *                     type="object",
     *                     @OA\Property(property="public_id", type="string", example="profiles/abcdef123456"),
     *                     @OA\Property(property="url", type="string", example="https://res.cloudinary.com/example/image/upload/v1234567890/profiles/abcdef123456.jpg")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="No file uploaded"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    #[Post("/upload-image-profile", "users.uploadImageProfile")]
    public function uploadImageProfile(Request $request)
    {
        $user = Auth::user();
        if (!$user) return $this->fail([], 'User not found',  401);
        if (!$request->hasFile('profile_image')) return $this->fail([], 'No file uploaded',  400);
        $validator = Validator::make($request->all(), [
            'profile_image' => 'required|image|mimes:jpg,jpeg,png|max:5120',
        ]);
        if ($validator->fails()) return $this->fail([], $validator->errors(), 422);
        $uploadedFile = $request->file('profile_image');
        try {
            $uploadedFile = $request->file('profile_image');
            $cloudinary = new Cloudinary();
            $uploadResponse = $cloudinary->uploadApi()->upload($uploadedFile->getRealPath(), [
                'folder' => 'profiles',
                'resource_type' => 'image',
            ]);
            /** @var \App\Models\User $user */
            $uploadFileUrl = $uploadResponse['secure_url'];
            $publicId = $uploadResponse['public_id'];
            $user->profile_image = [
                'public_id' => $publicId,
                'url' => $uploadFileUrl,
            ];
            $user->save();
            return $this->json([
                'profile_image' => $user->profile_image,
            ], 'Profile image uploaded successfully', 200);
        } catch (\Exception $e) {
            return $this->fail([], $e->getMessage(), 500);
        }
    }
}
