<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;
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
  #[Get(uri: "/", name: "admin.users.index", middleware: ["role:admin"])]
  /**
   * @OA\Get(
   *     path="/v1/admin/users",
   *     operationId="getUserList",
   *     tags={"Users"},
   *     summary="Lấy danh sách người dùng",
   *     description="Trả về danh sách tất cả người dùng trong hệ thống với khả năng tìm kiếm và phân trang",
   *     security={{"bearerAuth":{}}},
   *     @OA\Parameter(
   *         name="s",
   *         in="query",
   *         description="Từ khóa tìm kiếm (tìm theo username, email, firstname, lastname, phone)",
   *         required=false,
   *         @OA\Schema(type="string", example="nguyen")
   *     ),
   *     @OA\Parameter(
   *         name="per_page",
   *         in="query",
   *         description="Số lượng bản ghi trên mỗi trang",
   *         required=false,
   *         @OA\Schema(type="integer", example=10, default=10)
   *     ),
   *     @OA\Parameter(
   *         name="sort_by",
   *         in="query",
   *         description="Trường dùng để sắp xếp",
   *         required=false,
   *         @OA\Schema(type="string", enum={"username", "email", "firstname", "lastname", "role", "status", "created_at", "updated_at"}, default="created_at")
   *     ),
   *     @OA\Parameter(
   *         name="sort_order",
   *         in="query",
   *         description="Thứ tự sắp xếp",
   *         required=false,
   *         @OA\Schema(type="string", enum={"asc", "desc"}, default="desc")
   *     ),
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
    $perPage = $request->input('per_page', 10);
    $sortField = $request->input('sort_by', 'created_at');
    $sortOrder = $request->input('sort_order', 'desc');
    $search = $request->input('s', '');
    $role = $request->input('role', '');
    $status = $request->input('status', '');
    $allowedSortFields = ['username', 'email', 'firstname', 'lastname', 'role', 'status', 'created_at', 'updated_at'];
    if (!in_array($sortField, $allowedSortFields)) $sortField = 'created_at';
    $query = User::query();
    if (!empty($search)) {
      $query->where(function ($q) use ($search) {
        $q->where('username', 'like', "%{$search}%")
          ->orWhere('email', 'like', "%{$search}%")
          ->orWhere('firstname', 'like', "%{$search}%")
          ->orWhere('lastname', 'like', "%{$search}%")
          ->orWhere('phone', 'like', "%{$search}%");
      });
    }
    if (!empty($role)) $query->where('role', $role);
    if (!empty($status)) $query->where('status', $status);
    $users = $query->orderBy($sortField, $sortOrder === 'asc' ? 'asc' : 'desc')->paginate($perPage);
    return $this->json($users, "Lấy danh sách người dùng thành công");
  }

  #[Get(uri: "/statistics", name: "admin.users.statistics", middleware: ["role:admin"])]
  /**
   * @OA\Get(
   *     path="/v1/admin/users/statistics",
   *     operationId="getUserStatistics",
   *     tags={"Users"},
   *     summary="Lấy thống kê người dùng",
   *     description="Trả về thống kê tổng quan về người dùng trong hệ thống",
   *     security={{"bearerAuth":{}}},
   *     @OA\Response(
   *         response=200,
   *         description="Thành công",
   *         @OA\JsonContent(
   *             @OA\Property(property="data", type="object",
   *                 @OA\Property(property="total_users", type="integer", example=150, description="Tổng số người dùng"),
   *                 @OA\Property(property="admin_accounts", type="integer", example=5, description="Số tài khoản quản trị viên"),
   *                 @OA\Property(property="pharmacist_accounts", type="integer", example=20, description="Số tài khoản dược sĩ"),
   *                 @OA\Property(property="customer_accounts", type="integer", example=125, description="Số tài khoản khách hàng"),
   *                 @OA\Property(property="active_users", type="integer", example=140, description="Số người dùng đang hoạt động"),
   *                 @OA\Property(property="pending_users", type="integer", example=8, description="Số người dùng đang chờ xác thực"),
   *                 @OA\Property(property="suspended_users", type="integer", example=2, description="Số người dùng bị tạm khóa")
   *             ),
   *             @OA\Property(property="message", type="string", example="Lấy thống kê người dùng thành công"),
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
  public function userStats()
  {
    $totalUsers = User::count();
    $adminAccounts = User::where('role', UserRole::ADMIN)->count();
    $pharmacistAccounts = User::where('role', UserRole::PHARMACIST)->count();
    $customerAccounts = User::where('role', UserRole::CUSTOMER)->count();
    $activeUsers = User::where('status', UserStatus::ACTIVE)->count();
    $pendingUsers = User::where('status', UserStatus::PENDING)->count();
    $suspendedUsers = User::where('status', UserStatus::SUSPENDED)->count();
    $stats = [
      'total_users' => $totalUsers,
      'admin_accounts' => $adminAccounts,
      'pharmacist_accounts' => $pharmacistAccounts,
      'customer_accounts' => $customerAccounts,
      'active_users' => $activeUsers,
      'pending_users' => $pendingUsers,
      'suspended_users' => $suspendedUsers,
    ];
    return $this->json($stats, "Lấy thống kê người dùng thành công");
  }

  #[Get(uri: "/{id}/detail", name: "admin.users.detail", middleware: ["role:admin"])]
  /**
   * @OA\Get(
   *     path="/v1/admin/users/{id}/detail",
   *     operationId="getUserDetail",
   *     tags={"Users"},
   *     summary="Lấy chi tiết người dùng",
   *     description="Lấy thông tin chi tiết của một người dùng theo ID",
   *     security={{"bearerAuth":{}}},
   *     @OA\Parameter(
   *         name="id",
   *         in="path",
   *         required=true,
   *         description="ID của người dùng",
   *         @OA\Schema(type="string", example="65f1b3fc5bce7125f4001ec2")
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Lấy thông tin thành công",
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
   *             @OA\Property(property="message", type="string", example="Lấy thông tin người dùng thành công"),
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
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="Không tìm thấy người dùng",
   *         @OA\JsonContent(
   *             @OA\Property(property="data", type="null"),
   *             @OA\Property(property="message", type="string", example="Không tìm thấy người dùng"),
   *             @OA\Property(property="status", type="integer", example=404),
   *             @OA\Property(property="locale", type="string", example="vi_VN"),
   *             @OA\Property(property="error", type="object")
   *         )
   *     )
   * )
   */
  public function userDetail($id)
  {
    $user = User::find($id);
    return $this->json($user, "Lấy thông tin người dùng thành công");
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
      'role' => ['required', new Enum(UserRole::class)],
      'status' => ['required', new Enum(UserStatus::class)],
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
      'status' => $request->input('status'),
      'email_verified_at' => now(),
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
      'role' => ['nullable', new Enum(UserRole::class)],
      'status' => ['nullable', new Enum(UserStatus::class)],
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

  /**
   * @OA\Delete(
   *     path="/v1/admin/users/bulk-delete",
   *     operationId="bulkDeleteUsers",
   *     tags={"Users"},
   *     summary="Xóa nhiều người dùng",
   *     description="Xóa nhiều người dùng cùng lúc dựa trên danh sách ID được truyền qua query string",
   *     security={{"bearerAuth":{}}},
   *     @OA\Parameter(
   *         name="ids",
   *         in="query",
   *         description="Danh sách ID người dùng cần xóa, phân cách bằng dấu phẩy",
   *         required=true,
   *         @OA\Schema(type="string", example="65f1b3fc5bce7125f4001ec2,65f1b3fc5bce7125f4001ec3,65f1b3fc5bce7125f4001ec4")
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Xóa thành công",
   *         @OA\JsonContent(
   *             @OA\Property(property="data", type="object",
   *                 @OA\Property(property="deletedCount", type="integer", example=3, description="Số lượng người dùng đã xóa")
   *             ),
   *             @OA\Property(property="message", type="string", example="Xóa người dùng thành công"),
   *             @OA\Property(property="status", type="integer", example=200),
   *             @OA\Property(property="locale", type="string", example="vi_VN"),
   *             @OA\Property(property="error", type="object", nullable=true)
   *         )
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="Danh sách không tồn tại",
   *         @OA\JsonContent(
   *             @OA\Property(property="data", type="array", @OA\Items()),
   *             @OA\Property(property="message", type="string", example="Danh sách người dùng không tồn tại"),
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
  #[Delete(uri: "/bulk-delete", name: "admin.users.bulk-delete", middleware: "role:admin")]
  public function bulkDeleteUsers(Request $request)
  {
    $idsString = $request->query('ids');
    if (empty($idsString)) return $this->json([], "Danh sách người dùng không tồn tại", 404);
    $ids = explode(',', $idsString);
    $ids = array_filter($ids); // Loại bỏ các phần tử rỗng
    if (empty($ids)) return $this->json([], "Danh sách người dùng không tồn tại", 404);
    $deletedCount = User::whereIn('id', $ids)->count();
    User::whereIn('id', $ids)->delete();
    return $this->json(['deletedCount' => $deletedCount], "Xóa người dùng thành công");
  }
}
