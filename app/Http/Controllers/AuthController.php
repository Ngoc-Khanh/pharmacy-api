<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;
use Tymon\JWTAuth\Facades\JWTAuth;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="API endpoints cho việc xác thực và quản lý người dùng"
 * )
 */
#[Prefix("v2/auth")]
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v2/auth/register",
     *     summary="Đăng ký tài khoản mới",
     *     description="Tạo tài khoản người dùng mới và trả về token JWT",
     *     operationId="register",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *          required=true,
     *          description="Thông tin đăng ký người dùng",
     *          @OA\JsonContent(
     *              required={"username", "email", "password", "password_confirmation", "first_name", "last_name", "phone"},
     *              @OA\Property(property="username", type="string", example="johndoe", description="Tên đăng nhập"),
     *              @OA\Property(property="email", type="string", format="email", example="john@example.com", description="Địa chỉ email"),
     *              @OA\Property(property="password", type="string", format="password", example="secret123", description="Mật khẩu"),
     *              @OA\Property(property="password_confirmation", type="string", format="password", example="secret123", description="Xác nhận mật khẩu"),
     *              @OA\Property(property="first_name", type="string", example="John", description="Tên"),
     *              @OA\Property(property="last_name", type="string", example="Doe", description="Họ"),
     *              @OA\Property(property="phone", type="string", example="0901234567", description="Số điện thoại")
     *          )
     *     ),
     *     @OA\Response(
     *          response=201,
     *          description="Đăng ký thành công",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
     *                  @OA\Property(property="user", type="object",
     *                      @OA\Property(property="id", type="string", example="60a132ae1234567890abcdef"),
     *                      @OA\Property(property="username", type="string", example="johndoe"),
     *                      @OA\Property(property="email", type="string", example="john@example.com"),
     *                      @OA\Property(property="first_name", type="string", example="John"),
     *                      @OA\Property(property="last_name", type="string", example="Doe"),
     *                      @OA\Property(property="role", type="string", example="customer"),
     *                      @OA\Property(property="created_at", type="string", format="date-time")
     *                  )
     *              ),
     *              @OA\Property(property="message", type="string", example="User registered successfully"),
     *              @OA\Property(property="status", type="integer", example=201)
     *          )
     *     ),
     *     @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="object"),
     *              @OA\Property(property="message", type="object",
     *                  @OA\Property(property="username", type="array", @OA\Items(type="string", example="The username has already been taken.")),
     *                  @OA\Property(property="email", type="array", @OA\Items(type="string", example="The email has already been taken."))
     *              ),
     *              @OA\Property(property="status", type="integer", example=422)
     *          )
     *     )
     * )
     */
    #[Post("/register", "auth.register")]
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|confirmed|min:6',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'phone' => 'required|string',
        ]);
        if ($validator->fails()) return $this->json([], $validator->errors(), 422);
        $user = User::create([
            'username' => $request->get('username'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
            'first_name' => $request->get('first_name'),
            'last_name' => $request->get('last_name'),
            'phone' => $request->get('phone'),
            'profile_image' => collect(['1.jpg', '2.jpg', '3.jpg', '4.jpg', '5.jpg', '6.jpg'])->random(),
            'role' => 'customer',
            'status' => 'active',
        ]);
        $token = JWTAuth::fromUser($user);
        return $this->json([
            'access_token' => $token,
            'user' => $user,
        ], 'User registered successfully', 201);
    }

    /**
     * @OA\Post(
     *     path="/api/v2/auth/credentials",
     *     summary="Đăng nhập",
     *     description="Đăng nhập bằng tài khoản và mật khẩu, trả về token JWT",
     *     operationId="login",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *          required=true,
     *          description="Thông tin đăng nhập",
     *          @OA\JsonContent(
     *              required={"account", "password"},
     *              @OA\Property(property="account", type="string", example="johndoe", description="Username hoặc email"),
     *              @OA\Property(property="password", type="string", format="password", example="secret123", description="Mật khẩu")
     *          )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Đăng nhập thành công",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
     *                  @OA\Property(property="user", type="object",
     *                      @OA\Property(property="id", type="string", example="60a132ae1234567890abcdef"),
     *                      @OA\Property(property="username", type="string", example="johndoe"),
     *                      @OA\Property(property="email", type="string", example="john@example.com"),
     *                      @OA\Property(property="first_name", type="string", example="John"),
     *                      @OA\Property(property="last_name", type="string", example="Doe"),
     *                      @OA\Property(property="role", type="string", example="customer"),
     *                      @OA\Property(property="last_login_at", type="string", format="date-time")
     *                  )
     *              ),
     *              @OA\Property(property="message", type="string", example="Login successful"),
     *              @OA\Property(property="status", type="integer", example=200)
     *          )
     *     ),
     *     @OA\Response(
     *          response=401,
     *          description="Đăng nhập thất bại",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="object"),
     *              @OA\Property(property="message", type="string", example="Invalid credentials"),
     *              @OA\Property(property="status", type="integer", example=401)
     *          )
     *     ),
     *     @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="object"),
     *              @OA\Property(property="message", type="string", example="The account field is required."),
     *              @OA\Property(property="status", type="integer", example=422)
     *          )
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
        if ($validator->fails()) return $this->fail([], $validator->errors()->first(), 422);
        $accountField = filter_var($request->input('account'), FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $credentials = [
            $accountField => $request->input('account'),
            'password' => $request->input('password')
        ];
        if (!Auth::attempt($credentials)) return $this->fail([], 'Invalid credentials', 401);
        $user = Auth::user();
        if ($user->status !== 'active') return $this->fail([], 'Your account is inactive', 401);
        $user->last_login_at = Carbon::now();
        /** @var User $user */
        $user->save();
        $token = JWTAuth::fromUser($user);
        return $this->json([
            'access_token' => $token,
            'user' => $user,
        ], 'Login successful');
    }

    /**
     * @OA\Get(
     *     path="/api/v2/auth/me",
     *     summary="Lấy thông tin người dùng hiện tại",
     *     description="Trả về thông tin chi tiết của người dùng đã đăng nhập",
     *     operationId="getUserProfile",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *          response=200,
     *          description="Lấy thông tin thành công",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="string", example="60a132ae1234567890abcdef"),
     *                  @OA\Property(property="username", type="string", example="johndoe"),
     *                  @OA\Property(property="email", type="string", example="john@example.com"),
     *                  @OA\Property(property="first_name", type="string", example="John"),
     *                  @OA\Property(property="last_name", type="string", example="Doe"),
     *                  @OA\Property(property="phone", type="string", example="0901234567"),
     *                  @OA\Property(property="profile_image", type="string"),
     *                  @OA\Property(property="role", type="string", example="customer"),
     *                  @OA\Property(property="status", type="string", example="active"),
     *                  @OA\Property(property="created_at", type="string", format="date-time"),
     *                  @OA\Property(property="updated_at", type="string", format="date-time")
     *              ),
     *              @OA\Property(property="message", type="string", example="User profile retrieved successfully"),
     *              @OA\Property(property="status", type="integer", example=200)
     *          )
     *     ),
     *     @OA\Response(
     *          response=401,
     *          description="Không xác thực",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="object"),
     *              @OA\Property(property="message", type="string", example="Unauthenticated"),
     *              @OA\Property(property="status", type="integer", example=401)
     *          )
     *     )
     * )
     */
    #[Get("/me", "auth.me", "jwt.auth")]
    public function me(Request $request)
    {
        return $this->json($request->user(), "User profile retrieved successfully");
    }
}
