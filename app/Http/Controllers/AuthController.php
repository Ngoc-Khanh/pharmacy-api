<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;

/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="Các API xác thực người dùng"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Xác thực bằng JWT Token"
 * )
 */
#[Prefix("v1/auth")]
class AuthController extends Controller
{
  /**
   * @OA\Post(
   *     path="/v1/auth/register",
   *     operationId="register",
   *     tags={"Authentication"},
   *     summary="Đăng ký người dùng mới",
   *     description="Tạo tài khoản người dùng mới và trả về JWT token",
   *     @OA\RequestBody(
   *         required=true,
   *         @OA\JsonContent(
   *             required={"firstname", "lastname", "username", "email", "password", "phone"},
   *             @OA\Property(property="firstname", type="string", example="Nguyen"),
   *             @OA\Property(property="lastname", type="string", example="Van A"),
   *             @OA\Property(property="username", type="string", example="nguyenvana"),
   *             @OA\Property(property="email", type="string", format="email", example="nguyenvana@example.com"),
   *             @OA\Property(property="password", type="string", format="password", example="matkhau123"),
   *             @OA\Property(property="phone", type="string", example="0987654321")
   *         )
   *     ),
   *     @OA\Response(
   *         response=201,
   *         description="Đăng ký thành công",
   *         @OA\JsonContent(
   *             @OA\Property(property="success", type="boolean", example=true),
   *             @OA\Property(property="message", type="string", example="Đăng ký thành công"),
   *             @OA\Property(property="data", type="object",
   *                 @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
   *                 @OA\Property(property="user", type="object",
   *                     @OA\Property(property="id", type="string", example="60a1f2e6a1b9a2c3d4e5f6g7"),
   *                     @OA\Property(property="firstname", type="string", example="Nguyen"),
   *                     @OA\Property(property="lastname", type="string", example="Van A"),
   *                     @OA\Property(property="username", type="string", example="nguyenvana"),
   *                     @OA\Property(property="email", type="string", example="nguyenvana@example.com"),
   *                     @OA\Property(property="phone", type="string", example="0987654321"),
   *                     @OA\Property(property="profile_image", type="string", example="3.jpg"),
   *                     @OA\Property(property="role", type="string", example="customer"),
   *                     @OA\Property(property="status", type="string", example="pending"),
   *                     @OA\Property(property="created_at", type="string", format="date-time")
   *                 )
   *             )
   *         )
   *     ),
   *     @OA\Response(
   *         response=422,
   *         description="Lỗi xác thực",
   *         @OA\JsonContent(
   *             @OA\Property(property="success", type="boolean", example=false),
   *             @OA\Property(property="message", type="string", example="Lỗi xác thực"),
   *             @OA\Property(property="data", type="object", example={})
   *         )
   *     ),
   *     @OA\Response(
   *         response=500,
   *         description="Lỗi máy chủ",
   *         @OA\JsonContent(
   *             @OA\Property(property="success", type="boolean", example=false),
   *             @OA\Property(property="message", type="string", example="Đăng ký không thành công"),
   *             @OA\Property(property="data", type="object", example={})
   *         )
   *     )
   * )
   */
  #[Post("/register", "auth.register")]
  public function register(\App\Http\Requests\RegisterRequest $request)
  {
    $validated = $request->validated();
    if ($validated->fails()) return $this->fail([], $validated->errors(), 422);
    $user = User::create([
      'firstname' => $validated['firstname'],
      'lastname' => $validated['lastname'],
      'username' => $validated['username'],
      'email' => $validated['email'],
      'password' => bcrypt($validated['password']),
      'phone' => $validated['phone'],
      'profile_image' => collect(['1.jpg', '2.jpg', '3.jpg', '4.jpg', '5.jpg', '6.jpg'])->random(),
      'status' => UserStatus::PENDING->value,
      'role' => UserRole::CUSTOMER->value,
    ]);
    if (!$user) return $this->fail([], "Đăng ký không thành công", 500);
    $token = JWTAuth::fromUser($user);
    Log::info('New user registered', ['user_id' => $user->id, 'email' => $user->email]);
    return $this->json([
      'access_token' => $token,
      'user' => $user,
    ], "Đăng ký thành công", 201);
  }

  /**
   * @OA\Post(
   *     path="/v1/auth/credentials",
   *     operationId="login",
   *     tags={"Authentication"},
   *     summary="Đăng nhập bằng thông tin tài khoản",
   *     description="Xác thực người dùng bằng email/tên đăng nhập và mật khẩu, trả về JWT token",
   *     @OA\RequestBody(
   *         required=true,
   *         @OA\JsonContent(
   *             required={"account", "password"},
   *             @OA\Property(property="account", type="string", example="johndoe", description="Email hoặc tên đăng nhập"),
   *             @OA\Property(property="password", type="string", format="password", example="password123")
   *         )
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Đăng nhập thành công",
   *         @OA\JsonContent(
   *             @OA\Property(property="success", type="boolean", example=true),
   *             @OA\Property(property="message", type="string", example="Đăng nhập thành công"),
   *             @OA\Property(property="data", type="object",
   *                 @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
   *                 @OA\Property(property="user", type="object",
   *                     @OA\Property(property="id", type="string", example="60a1f2e6a1b9a2c3d4e5f6g7"),
   *                     @OA\Property(property="firstname", type="string", example="John"),
   *                     @OA\Property(property="lastname", type="string", example="Doe"),
   *                     @OA\Property(property="username", type="string", example="johndoe"),
   *                     @OA\Property(property="email", type="string", example="john@example.com"),
   *                     @OA\Property(property="phone", type="string", example="0123456789"),
   *                     @OA\Property(property="last_login_at", type="string", format="date-time")
   *                 )
   *             )
   *         )
   *     ),
   *     @OA\Response(
   *         response=401,
   *         description="Xác thực thất bại",
   *         @OA\JsonContent(
   *             @OA\Property(property="success", type="boolean", example=false),
   *             @OA\Property(property="message", type="string", example="Tài khoản hoặc mật khẩu không chính xác"),
   *             @OA\Property(property="data", type="null", example=null)
   *         )
   *     ),
   *     @OA\Response(
   *         response=403,
   *         description="Tài khoản không hoạt động",
   *         @OA\JsonContent(
   *             @OA\Property(property="success", type="boolean", example=false),
   *             @OA\Property(property="message", type="string", example="Tài khoản chưa được kích hoạt"),
   *             @OA\Property(property="data", type="null", example=null)
   *         )
   *     ),
   *     @OA\Response(
   *         response=422,
   *         description="Lỗi xác thực",
   *         @OA\JsonContent(
   *             @OA\Property(property="success", type="boolean", example=false),
   *             @OA\Property(property="message", type="string", example="Trường account là bắt buộc."),
   *             @OA\Property(property="data", type="null", example=null)
   *         )
   *     )
   * )
   */
  #[Post("/credentials", "auth.credentials")]
  public function credentials(Request $request)
  {
    $validator = Validator::make($request->only(['account', 'password']), [
      'account' => 'required|string',
      'password' => 'required|string',
    ]);
    if ($validator->fails()) return $this->fail(null, $validator->errors()->first(), 422);
    $accountField = filter_var($request->account, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
    $credentials = [
      $accountField => $request->account,
      'password' => $request->password,
    ];
    if (!Auth::attempt($credentials)) return $this->fail(null, "Tài khoản hoặc mật khẩu không chính xác", 401);
    $user = Auth::user();
    // Check user status with match expression for cleaner code
    if ($user->status !== UserStatus::ACTIVE->value) {
      $message = match ($user->status) {
        UserStatus::PENDING->value => "Tài khoản chưa được kích hoạt",
        UserStatus::SUSPENDED->value => "Tài khoản đã bị khóa",
        default => "Tài khoản không trong trạng thái hoạt động",
      };
      return $this->fail(null, $message, 403);
    }
    $user->last_login_at = now();
    /** @var User $user */
    $user->save();
    $token = JWTAuth::fromUser($user);
    return $this->json([
      'access_token' => $token,
      'user' => $user,
    ], "Đăng nhập thành công");
  }
}
