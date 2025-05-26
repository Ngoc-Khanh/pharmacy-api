<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\RouteAttributes\Attributes\Delete;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Patch;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Middleware;

/**
 * @OA\Tag(
 *     name="Users",
 *     description="Quản lý người dùng hệ thống"
 * )
 */
#[Prefix(prefix: "v1/admin/users")]
#[Middleware(middleware: "jwt.auth")]
class UserController extends Controller
{
  #[Get(uri: "/", name: "admin.users.index", middleware: ["role:admin,pharmacist"])]
  /**
   * @OA\Get(
   *     path="/v1/admin/users",
   *     operationId="getUserList",
   *     tags={"Users"},
   *     summary="Lấy danh sách người dùng",
   *     description="Trả về danh sách tất cả người dùng trong hệ thống",
   *     security={{"bearerAuth":{}}},
   *     @OA\Response(
   *         response=200,
   *         description="Thành công",
   *         @OA\JsonContent(
   *             @OA\Property(property="data", type="array", 
   *                @OA\Items(
   *                    @OA\Property(property="id", type="string", example="65f1b3fc5bce7125f4001ec2"),
   *                    @OA\Property(property="username", type="string", example="user1"),
   *                    @OA\Property(property="email", type="string", example="user1@example.com"),
   *                    @OA\Property(property="firstname", type="string", example="Nguyen"),
   *                    @OA\Property(property="lastname", type="string", example="Van A"),
   *                    @OA\Property(property="phone", type="string", example="0901234567"),
   *                    @OA\Property(property="profile_image", type="string", example="1.jpg"),
   *                    @OA\Property(property="role", type="string", example="customer"),
   *                    @OA\Property(property="status", type="string", example="active"),
   *                    @OA\Property(property="created_at", type="string", format="date-time"),
   *                    @OA\Property(property="updated_at", type="string", format="date-time")
   *                )
   *             ),
   *             @OA\Property(property="message", type="string", example="Lấy danh sách người dùng thành công"),
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
  public function userList(Request $request)
  {
    $perPage = $request->input('per_page', 15);
    $sortField = $request->input('sort_by', 'created_at');
    $sortOrder = $request->input('sort_order', 'desc');
    $allowedSortFields = ['username', 'email', 'firstname', 'lastname', 'role', 'status', 'created_at', 'updated_at'];
    if (!in_array($sortField, $allowedSortFields)) {
      $sortField = 'created_at';
    }
    $users = User::orderBy($sortField, $sortOrder === 'asc' ? 'asc' : 'desc')
      ->paginate($perPage);
    return $this->json($users, "Lấy danh sách người dùng thành công");
  }

  /**
   * @OA\Post(
   *     path="/v1/admin/users/add",
   *     operationId="addUser",
   *     tags={"Users"},
   *     summary="Thêm người dùng mới",
   *     description="Tạo một người dùng mới trong hệ thống",
   *     security={{"bearerAuth":{}}},
   *     @OA\RequestBody(
   *         required=true,
   *         @OA\JsonContent(
   *             required={"username", "email", "password", "password_confirmation", "role", "status"},
   *             @OA\Property(property="firstname", type="string", example="Nguyen", description="Tên của người dùng"),
   *             @OA\Property(property="lastname", type="string", example="Van A", description="Họ của người dùng"),
   *             @OA\Property(property="username", type="string", example="user1", description="Tên đăng nhập (độc nhất)"),
   *             @OA\Property(property="email", type="string", format="email", example="user1@example.com", description="Email người dùng (độc nhất)"),
   *             @OA\Property(property="password", type="string", format="password", example="Password123!", description="Mật khẩu (ít nhất 8 ký tự, gồm chữ hoa, chữ thường, số và ký tự đặc biệt)"),
   *             @OA\Property(property="password_confirmation", type="string", format="password", example="Password123!", description="Xác nhận mật khẩu"),
   *             @OA\Property(property="phone", type="string", example="0901234567", description="Số điện thoại (độc nhất)"),
   *             @OA\Property(property="role", type="string", example="customer", description="Vai trò (customer/pharmacist/admin)"),
   *             @OA\Property(property="status", type="string", example="active", description="Trạng thái tài khoản (active/suspended/pending)")
   *         )
   *     ),
   *     @OA\Response(
   *         response=201,
   *         description="Tạo thành công",
   *         @OA\JsonContent(
   *             @OA\Property(property="data", type="object",
   *                 @OA\Property(property="id", type="string", example="65f1b3fc5bce7125f4001ec2"),
   *                 @OA\Property(property="username", type="string", example="user1"),
   *                 @OA\Property(property="email", type="string", example="user1@example.com"),
   *                 @OA\Property(property="firstname", type="string", example="Nguyen"),
   *                 @OA\Property(property="lastname", type="string", example="Van A"),
   *                 @OA\Property(property="phone", type="string", example="0901234567"),
   *                 @OA\Property(property="profile_image", type="string", example="1.jpg"),
   *                 @OA\Property(property="role", type="string", example="customer"),
   *                 @OA\Property(property="status", type="string", example="active"),
   *                 @OA\Property(property="created_at", type="string", format="date-time"),
   *                 @OA\Property(property="updated_at", type="string", format="date-time")
   *             ),
   *             @OA\Property(property="message", type="string", example="User created successfully"),
   *             @OA\Property(property="status", type="integer", example=201),
   *             @OA\Property(property="locale", type="string", example="vi_VN"),
   *             @OA\Property(property="error", type="object", nullable=true)
   *         )
   *     ),
   *     @OA\Response(
   *         response=422,
   *         description="Dữ liệu không hợp lệ",
   *         @OA\JsonContent(
   *             @OA\Property(property="data", type="object",
   *                 @OA\Property(property="username", type="array", @OA\Items(type="string", example="Tên đăng nhập đã tồn tại")),
   *                 @OA\Property(property="email", type="array", @OA\Items(type="string", example="Email đã được sử dụng")),
   *                 @OA\Property(property="password", type="array", @OA\Items(type="string", example="Mật khẩu phải có ít nhất 8 ký tự"))
   *             ),
   *             @OA\Property(property="message", type="string", example="Dữ liệu không hợp lệ"),
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
  #[Post(uri: "/add", name: "admin.users.add", middleware: ["role:admin"])]
  public function addUser(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'firstname' => 'nullable|string|max:255',
      'lastname' => 'nullable|string|max:255',
      'username' => 'required|string|min:3|max:255|unique:users',
      'email' => 'required|string|email|max:255|unique:users',
      'password' => [
        'required',
        'string',
        'min:8',
        'confirmed',
        'regex:/[a-z]/',      // Ít nhất một chữ cái thường
        'regex:/[A-Z]/',      // Ít nhất một chữ cái hoa
        'regex:/[0-9]/',      // Ít nhất một số
        'regex:/[@$!%*#?&]/', // Ít nhất một ký tự đặc biệt
      ],
      'phone' => 'nullable|string|max:15|unique:users',
      'role' => 'required|string|in:customer,pharmacist,admin',
      'status' => 'required|string|in:active,suspended,pending',
    ]);
    if ($validator->fails()) return $this->json($validator->errors(), "Dữ liệu không hợp lệ", 422);
    $newUser = User::create([
      'username' => $request->input('username'),
      'email' => $request->input('email'),
      'firstname' => $request->input('firstname') ?? 'User' . rand(1000, 9999),
      'lastname' => $request->input('lastname') ?? 'Guest' . rand(1000, 9999),
      'password' => bcrypt($request->input('password')),
      'phone' => $request->input('phone'),
      'profile_image' => [
        'public_id' => null,
        'url' => "./avatars/" . collect(['1.jpg', '2.jpg', '3.jpg', '4.jpg', '5.jpg', '6.jpg', '7.jpg', '8.jpg'])->random(),
        'alt' => $request->input('username') . "-alt",
      ],
      'role' => $request->input('role'),
      'status' => $request->input('status')
    ]);
    return $this->json($newUser, 'User created successfully', 201);
  }

  /**
   * @OA\Patch(
   *     path="/v1/admin/users/update/{id}",
   *     operationId="updateUser",
   *     tags={"Users"},
   *     summary="Cập nhật thông tin người dùng",
   *     description="Cập nhật chi tiết của một người dùng hiện có bằng ID của họ.",
   *     security={{"bearerAuth":{}}},
   *     @OA\Parameter(
   *         name="id",
   *         in="path",
   *         description="ID của người dùng cần cập nhật",
   *         required=true,
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\RequestBody(
   *         required=true,
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(property="firstname", type="string", maxLength=255, nullable=true, description="Tên của người dùng"),
   *             @OA\Property(property="lastname", type="string", maxLength=255, nullable=true, description="Họ của người dùng"),
   *             @OA\Property(property="username", type="string", minLength=3, maxLength=255, nullable=true, description="Tên đăng nhập duy nhất của người dùng"),
   *             @OA\Property(property="email", type="string", format="email", maxLength=255, nullable=true, description="Địa chỉ email duy nhất của người dùng"),
   *             @OA\Property(property="password", type="string", minLength=8, nullable=true, description="Mật khẩu với ít nhất một chữ cái thường, một chữ cái hoa, một số và một ký tự đặc biệt"),
   *             @OA\Property(property="password_confirmation", type="string", nullable=true, description="Xác nhận mật khẩu"),
   *             @OA\Property(property="phone", type="string", maxLength=15, nullable=true, description="Số điện thoại duy nhất của người dùng"),
   *             @OA\Property(property="role", type="string", enum={"customer", "pharmacist", "admin"}, nullable=true, description="Vai trò của người dùng"),
   *             @OA\Property(property="status", type="string", enum={"active", "suspended", "pending"}, nullable=true, description="Trạng thái của người dùng")
   *         )
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Cập nhật người dùng thành công",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(property="data", type="object", description="Dữ liệu người dùng đã cập nhật"),
   *             @OA\Property(property="message", type="string", example="Cập nhật người dùng thành công")
   *         )
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="Người dùng không tồn tại",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(property="data", type="array", @OA\Items(type="string")),
   *             @OA\Property(property="message", type="string", example="Người dùng không tồn tại")
   *         )
   *     ),
   *     @OA\Response(
   *         response=422,
   *         description="Lỗi xác thực",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(property="data", type="object", description="Lỗi xác thực"),
   *             @OA\Property(property="message", type="string", example="Dữ liệu không hợp lệ")
   *         )
   *     )
   * )
   */
  #[Patch(uri: "/update/{id}", name: "admin.users.update", middleware: ["role:admin,pharmacist"])]
  public function updateUser(Request $request, $id)
  {
    $user = User::find($id);
    if (!$user) return $this->json([], "Người dùng không tồn tại", 404);
    $validator = Validator::make($request->all(), [
      'firstname' => 'nullable|string|max:255',
      'lastname' => 'nullable|string|max:255',
      'username' => 'nullable|string|min:3|max:255|unique:users,username,' . $id,
      'email' => 'nullable|string|email|max:255|unique:users,email,' . $id,
      'password' => [
        'nullable',
        'string',
        'min:8',
        'confirmed',
        'regex:/[a-z]/',      // Ít nhất một chữ cái thường
        'regex:/[A-Z]/',      // Ít nhất một chữ cái hoa
        'regex:/[0-9]/',      // Ít nhất một số
        'regex:/[@$!%*#?&]/', // Ít nhất một ký tự đặc biệt
      ],
      'phone' => 'nullable|string|max:15|unique:users,phone,' . $id,
      'role' => 'nullable|string|in:customer,pharmacist,admin',
      'status' => 'nullable|string|in:active,suspended,pending',
    ]);
    if ($validator->fails()) return $this->json($validator->errors(), "Dữ liệu không hợp lệ", 422);
    $updateData = array_filter($request->only([
      'firstname',
      'lastname',
      'username',
      'email',
      'password',
      'phone',
      'role',
      'status'
    ]), function ($value) {
      return !is_null($value);
    });
    if (isset($updateData['password'])) $updateData['password'] = bcrypt($updateData['password']);
    $user->update($updateData);
    return $this->json($user, "Cập nhật người dùng thành công");
  }

  /**
   * @OA\Delete(
   *     path="/v1/admin/users/delete/{id}",
   *     operationId="deleteUser",
   *     tags={"Users"},
   *     summary="Xóa người dùng",
   *     description="Xóa người dùng khỏi hệ thống theo ID",
   *     security={{"bearerAuth":{}}},
   *     @OA\Parameter(
   *         name="id",
   *         in="path",
   *         description="ID của người dùng cần xóa",
   *         required=true,
   *         @OA\Schema(type="string")
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Xóa thành công",
   *         @OA\JsonContent(
   *             @OA\Property(property="data", type="null"),
   *             @OA\Property(property="message", type="string", example="Xóa người dùng thành công"),
   *             @OA\Property(property="status", type="integer", example=200),
   *             @OA\Property(property="locale", type="string", example="vi_VN"),
   *             @OA\Property(property="error", type="object", nullable=true)
   *         )
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="Không tìm thấy người dùng",
   *         @OA\JsonContent(
   *             @OA\Property(property="data", type="array", @OA\Items()),
   *             @OA\Property(property="message", type="string", example="Người dùng không tồn tại"),
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
  #[Delete(uri: "/delete/{id}", name: "admin.users.delete", middleware: "role:admin")]
  public function deleteUser($id)
  {
    $user = User::find($id);
    if (!$user) return $this->json([], "Người dùng không tồn tại", 404);
    $user->delete();
    return $this->json(null, "Xóa người dùng thành công");
  }
}
