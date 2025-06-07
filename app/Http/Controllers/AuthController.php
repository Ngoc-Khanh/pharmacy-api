<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgotPasswordRequest;
use App\Models\User;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Requests\ResetPasswordRequest;
use App\Notifications\EmailVerificationNotification;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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
  #[Get("/me", "auth.me")]
  /**
   * @OA\Get(
   *     path="/v1/auth/me",
   *     operationId="getAuthenticatedUser",
   *     tags={"Authentication"},
   *     summary="Lấy thông tin người dùng đã đăng nhập",
   *     description="Trả về thông tin chi tiết của người dùng hiện tại dựa trên JWT token",
   *     security={{"bearerAuth":{}}},
   *     @OA\Response(
   *         response=200,
   *         description="Lấy thông tin thành công",
   *         @OA\JsonContent(
   *             @OA\Property(property="success", type="boolean", example=true),
   *             @OA\Property(property="message", type="string", example="Lấy thông tin người dùng thành công"),
   *             @OA\Property(property="data", type="object",
   *                 @OA\Property(property="_id", type="string", example="60a1f2e6a1b9a2c3d4e5f6g7"),
   *                 @OA\Property(property="firstname", type="string", example="Nguyen"),
   *                 @OA\Property(property="lastname", type="string", example="Van A"),
   *                 @OA\Property(property="username", type="string", example="nguyenvana"),
   *                 @OA\Property(property="email", type="string", example="nguyenvana@example.com"),
   *                 @OA\Property(property="phone", type="string", example="0987654321"),
   *                 @OA\Property(property="profile_image", type="string", example="3.jpg"),
   *                 @OA\Property(property="role", type="string", example="customer"),
   *                 @OA\Property(property="status", type="string", example="active")
   *             )
   *         )
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="Người dùng không tồn tại",
   *         @OA\JsonContent(
   *             @OA\Property(property="success", type="boolean", example=false),
   *             @OA\Property(property="message", type="string", example="Người dùng không tồn tại"),
   *             @OA\Property(property="data", type="null", example=null)
   *         )
   *     ),
   *     @OA\Response(
   *         response=401,
   *         description="Chưa xác thực",
   *         @OA\JsonContent(
   *             @OA\Property(property="success", type="boolean", example=false),
   *             @OA\Property(property="message", type="string", example="Unauthenticated"),
   *             @OA\Property(property="data", type="null", example=null)
   *         )
   *     )
   * )
   */
  public function me()
  {
    $user = Auth::user();
    if (!$user) return $this->fail(null, "Người dùng không tồn tại", 404);
    return $this->json($user, "Lấy thông tin người dùng thành công", 200);
  }

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
    $verificationCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $user = User::create([
      'firstname' => $validated['firstname'],
      'lastname' => $validated['lastname'],
      'username' => $validated['username'],
      'email' => $validated['email'],
      'password' => bcrypt($validated['password']),
      'phone' => $validated['phone'],
      'profile_image' => [
        'public_id' => null,
        'url' => "./avatars/" . collect(['1.jpg', '2.jpg', '3.jpg', '4.jpg', '5.jpg', '6.jpg', '7.jpg', '8.jpg'])->random(),
        'alt' => $validated['username'] . "-alt",
      ],
      'status' => UserStatus::PENDING->value,
      'role' => UserRole::CUSTOMER->value,
      'verification_code' => $verificationCode,
      'verification_code_expires_at' => now()->addMinutes(15),
    ]);
    if (!$user) return $this->fail([], "Đăng ký không thành công", 500);
    $user->notify(new EmailVerificationNotification($verificationCode));
    $token = JWTAuth::fromUser($user);
    Log::info('New user registered', ['user_id' => $user->id, 'email' => $user->email]);
    return $this->json([
      'access_token' => $token,
      'user' => $user,
      'required_verification' => true,
    ], "Đăng ký thành công. Vui lòng kiểm tra email để xác minh tài khoản.", 201);
  }

  /**
   * @OA\Post(
   *     path="/v1/auth/verify-email",
   *     operationId="verifyEmail",
   *     tags={"Authentication"},
   *     summary="Xác minh email bằng mã xác nhận",
   *     description="Xác minh email của người dùng bằng mã 6 chữ số được gửi qua email",
   *     security={{"bearerAuth":{}}},
   *     @OA\RequestBody(
   *         required=true,
   *         @OA\JsonContent(
   *             required={"verification_code"},
   *             @OA\Property(property="verification_code", type="string", example="123456", description="Mã xác minh 6 chữ số")
   *         )
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Xác minh email thành công",
   *         @OA\JsonContent(
   *             @OA\Property(property="success", type="boolean", example=true),
   *             @OA\Property(property="message", type="string", example="Xác minh email thành công"),
   *             @OA\Property(property="data", type="object",
   *                 @OA\Property(property="_id", type="string", example="60a1f2e6a1b9a2c3d4e5f6g7"),
   *                 @OA\Property(property="firstname", type="string", example="Nguyen"),
   *                 @OA\Property(property="lastname", type="string", example="Van A"),
   *                 @OA\Property(property="username", type="string", example="nguyenvana"),
   *                 @OA\Property(property="email", type="string", example="nguyenvana@example.com"),
   *                 @OA\Property(property="phone", type="string", example="0987654321"),
   *                 @OA\Property(property="profile_image", type="string", example="3.jpg"),
   *                 @OA\Property(property="role", type="string", example="customer"),
   *                 @OA\Property(property="status", type="string", example="active"),
   *                 @OA\Property(property="email_verified_at", type="string", format="date-time")
   *             )
   *         )
   *     ),
   *     @OA\Response(
   *         response=400,
   *         description="Mã xác minh không hợp lệ hoặc đã hết hạn",
   *         @OA\JsonContent(
   *             @OA\Property(property="success", type="boolean", example=false),
   *             @OA\Property(property="message", type="string", example="Mã xác minh không chính xác"),
   *             @OA\Property(property="data", type="null", example=null)
   *         )
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="Người dùng không tồn tại",
   *         @OA\JsonContent(
   *             @OA\Property(property="success", type="boolean", example=false),
   *             @OA\Property(property="message", type="string", example="Người dùng không tồn tại"),
   *             @OA\Property(property="data", type="null", example=null)
   *         )
   *     ),
   *     @OA\Response(
   *         response=422,
   *         description="Lỗi xác thực dữ liệu đầu vào",
   *         @OA\JsonContent(
   *             @OA\Property(property="success", type="boolean", example=false),
   *             @OA\Property(property="message", type="string", example="Trường verification code là bắt buộc."),
   *             @OA\Property(property="data", type="null", example=null)
   *         )
   *     ),
   *     @OA\Response(
   *         response=401,
   *         description="Chưa xác thực",
   *         @OA\JsonContent(
   *             @OA\Property(property="success", type="boolean", example=false),
   *             @OA\Property(property="message", type="string", example="Unauthenticated"),
   *             @OA\Property(property="data", type="null", example=null)
   *         )
   *     )
   * )
   */
  #[Post('/verify-email', 'auth.verifyEmail')]
  public function verifyEmail(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'verification_code' => 'required|string|size:6',
    ]);
    if ($validator->fails()) return $this->fail(null, $validator->errors()->first(), 422);
    /** @var User */
    $user = Auth::user();
    if (!$user) return $this->fail(null, "Người dùng không tồn tại", 404);
    if ($user->hasVerifiedEmail()) return $this->fail(null, "Email đã được xác minh trước đó", 400);
    if (!$user->verification_code || $user->verification_code !== $request->verification_code) {
      now()->isAfter($user->verification_code_expires_at) ?
        $this->fail(null, "Mã xác minh đã hết hạn", 400) :
        $this->fail(null, "Mã xác minh không chính xác", 400);
      return $this->fail(null, "Mã xác minh không chính xác", 400);
    }
    $user->markEmailAsVerified();
    $user->status = UserStatus::ACTIVE->value;
    $user->save();
    Log::info('User email verified', ['user_id' => $user->id, 'email' => $user->email]);
    return $this->json($user, "Xác minh email thành công", 200);
  }

  #[Post("/resend-verification-email", "auth.resendVerificationEmail")]
  /**
   * @OA\Post(
   *     path="/v1/auth/resend-verification-email",
   *     operationId="resendVerificationEmail",
   *     tags={"Authentication"},
   *     summary="Gửi lại email xác minh",
   *     description="Gửi lại mã xác minh email cho người dùng chưa xác minh tài khoản",
   *     security={{"bearerAuth":{}}},
   *     @OA\Response(
   *         response=200,
   *         description="Gửi lại email xác minh thành công",
   *         @OA\JsonContent(
   *             @OA\Property(property="success", type="boolean", example=true),
   *             @OA\Property(property="message", type="string", example="Đã gửi lại email xác minh. Vui lòng kiểm tra hộp thư đến của bạn."),
   *             @OA\Property(property="data", type="null", example=null)
   *         )
   *     ),
   *     @OA\Response(
   *         response=400,
   *         description="Email đã được xác minh",
   *         @OA\JsonContent(
   *             @OA\Property(property="success", type="boolean", example=false),
   *             @OA\Property(property="message", type="string", example="Email đã được xác minh trước đó"),
   *             @OA\Property(property="data", type="null", example=null)
   *         )
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="Người dùng không tồn tại",
   *         @OA\JsonContent(
   *             @OA\Property(property="success", type="boolean", example=false),
   *             @OA\Property(property="message", type="string", example="Người dùng không tồn tại"),
   *             @OA\Property(property="data", type="null", example=null)
   *         )
   *     ),
   *     @OA\Response(
   *         response=429,
   *         description="Quá nhiều yêu cầu",
   *         @OA\JsonContent(
   *             @OA\Property(property="success", type="boolean", example=false),
   *             @OA\Property(property="message", type="string", example="Vui lòng chờ 60 giây trước khi gửi lại"),
   *             @OA\Property(property="data", type="null", example=null)
   *         )
   *     ),
   *     @OA\Response(
   *         response=401,
   *         description="Chưa xác thực",
   *         @OA\JsonContent(
   *             @OA\Property(property="success", type="boolean", example=false),
   *             @OA\Property(property="message", type="string", example="Unauthenticated"),
   *             @OA\Property(property="data", type="null", example=null)
   *         )
   *     )
   * )
   */
  public function resendVerificationEmail()
  {
    /** @var User */
    $user = Auth::user();
    if (!$user) return $this->fail(null, "Người dùng không tồn tại", 404);
    if ($user->hasVerifiedEmail()) return $this->fail(null, "Email đã được xác minh trước đó", 400);
    $cacheKey = "resend_verification_{$user->id}";
    if (Cache::has($cacheKey)) return $this->fail(null, "Vui lòng chờ 60 giây trước khi gửi lại", 429);
    $verificationCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $user->verification_code = $verificationCode;
    $user->verification_code_expires_at = now()->addMinutes(15);
    $user->save();
    $user->notify(new EmailVerificationNotification($verificationCode));
    Cache::put($cacheKey, true, 60); // Cache for 60 seconds
    Log::info('Resent verification email', ['user_id' => $user->id, 'email' => $user->email]);
    return $this->json(null, "Đã gửi lại email xác minh. Vui lòng kiểm tra hộp thư đến của bạn.", 200);
  }

  /**
   * @OA\Post(
   *     path="/v1/auth/forgot-password",
   *     operationId="forgotPassword",
   *     tags={"Authentication"},
   *     summary="Yêu cầu đặt lại mật khẩu",
   *     description="Gửi email chứa liên kết đặt lại mật khẩu cho người dùng",
   *     @OA\RequestBody(
   *         required=true,
   *         @OA\JsonContent(
   *             required={"email"},
   *             @OA\Property(property="email", type="string", format="email", example="user@example.com", description="Email của tài khoản cần đặt lại mật khẩu")
   *         )
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Gửi email đặt lại mật khẩu thành công",
   *         @OA\JsonContent(
   *             @OA\Property(property="success", type="boolean", example=true),
   *             @OA\Property(property="message", type="string", example="Đã gửi email hướng dẫn đặt lại mật khẩu. Vui lòng kiểm tra hộp thư của bạn."),
   *             @OA\Property(property="data", type="null", example=null)
   *         )
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="Email không tồn tại",
   *         @OA\JsonContent(
   *             @OA\Property(property="success", type="boolean", example=false),
   *             @OA\Property(property="message", type="string", example="Email không tồn tại trong hệ thống"),
   *             @OA\Property(property="data", type="null", example=null)
   *         )
   *     ),
   *     @OA\Response(
   *         response=422,
   *         description="Lỗi xác thực dữ liệu",
   *         @OA\JsonContent(
   *             @OA\Property(property="success", type="boolean", example=false),
   *             @OA\Property(property="message", type="string", example="Trường email là bắt buộc."),
   *             @OA\Property(property="data", type="null", example=null)
   *         )
   *     ),
   *     @OA\Response(
   *         response=429,
   *         description="Quá nhiều yêu cầu",
   *         @OA\JsonContent(
   *             @OA\Property(property="success", type="boolean", example=false),
   *             @OA\Property(property="message", type="string", example="Vui lòng chờ 60 giây trước khi gửi lại yêu cầu đặt lại mật khẩu"),
   *             @OA\Property(property="data", type="null", example=null)
   *         )
   *     )
   * )
   */
  #[Post("/forgot-password", "auth.forgotPassword")]
  public function forgotPassword(ForgotPasswordRequest $request)
  {
    $email = $request->validated()['email'];
    $cacheKey = "forgot_password_{$email}";
    if (Cache::has($cacheKey)) return $this->fail(null, "Vui lòng chờ 60 giây trước khi gửi lại yêu cầu đặt lại mật khẩu", 429);
    $user = User::where('email', $email)->first();
    $resetToken = Str::uuid()->toString();
    $user->password_reset_code = $resetToken;
    $user->password_reset_expires_at = now()->addMinutes(15);
    $user->save();
    $user->notify(new ResetPasswordNotification($resetToken, $user->firstname . ' ' . $user->lastname));
    Cache::put($cacheKey, true, 60);
    Log::info('Password reset requested', [
      'user_id' => $user->id,
      'email' => $user->email,
      'reset_token' => $resetToken
    ]);
    return $this->json(null, "Đã gửi email hướng dẫn đặt lại mật khẩu. Vui lòng kiểm tra hộp thư của bạn.", 200);
  }

  /**
   * @OA\Post(
   *     path="/v1/auth/reset-password",
   *     operationId="resetPassword",
   *     tags={"Authentication"},
   *     summary="Đặt lại mật khẩu",
   *     description="Đặt lại mật khẩu mới cho tài khoản bằng token từ email",
   *     @OA\RequestBody(
   *         required=true,
   *         @OA\JsonContent(
   *             required={"reset_token", "password", "password_confirmation"},
   *             @OA\Property(property="reset_token", type="string", example="550e8400-e29b-41d4-a716-446655440000", description="Token đặt lại mật khẩu từ email"),
   *             @OA\Property(property="password", type="string", format="password", example="newpassword123", description="Mật khẩu mới"),
   *             @OA\Property(property="password_confirmation", type="string", format="password", example="newpassword123", description="Xác nhận mật khẩu mới")
   *         )
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Đặt lại mật khẩu thành công",
   *         @OA\JsonContent(
   *             @OA\Property(property="success", type="boolean", example=true),
   *             @OA\Property(property="message", type="string", example="Mật khẩu đã được đặt lại thành công"),
   *             @OA\Property(property="data", type="object",
   *                 @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
   *                 @OA\Property(property="user", type="object",
   *                     @OA\Property(property="id", type="string", example="60a1f2e6a1b9a2c3d4e5f6g7"),
   *                     @OA\Property(property="firstname", type="string", example="John"),
   *                     @OA\Property(property="lastname", type="string", example="Doe"),
   *                     @OA\Property(property="username", type="string", example="johndoe"),
   *                     @OA\Property(property="email", type="string", example="john@example.com"),
   *                     @OA\Property(property="phone", type="string", example="0123456789")
   *                 )
   *             )
   *         )
   *     ),
   *     @OA\Response(
   *         response=400,
   *         description="Token không hợp lệ hoặc đã hết hạn",
   *         @OA\JsonContent(
   *             @OA\Property(property="success", type="boolean", example=false),
   *             @OA\Property(property="message", type="string", example="Mã đặt lại mật khẩu không hợp lệ hoặc đã hết hạn"),
   *             @OA\Property(property="data", type="null", example=null)
   *         )
   *     ),
   *     @OA\Response(
   *         response=422,
   *         description="Lỗi xác thực dữ liệu",
   *         @OA\JsonContent(
   *             @OA\Property(property="success", type="boolean", example=false),
   *             @OA\Property(property="message", type="string", example="Trường password là bắt buộc."),
   *             @OA\Property(property="data", type="null", example=null)
   *         )
   *     )
   * )
   */
  #[Post('/reset-password', 'auth.resetPassword')]
  public function resetPassword(ResetPasswordRequest $request)
  {
    $validated = $request->validated();
    $user = User::where('password_reset_code', $validated['reset_token'])
      ->where('password_reset_expires_at', '>', now())
      ->first();
    if (!$user) return $this->fail(null, "Mã đặt lại mật khẩu không hợp lệ hoặc đã hết hạn", 400);
    $user->password = bcrypt($validated['password']);
    unset($user->password_reset_code);
    unset($user->password_reset_expires_at);
    $user->save();
    $token = JWTAuth::fromUser($user);
    Log::info('Password reset successfully', [
      'user_id' => $user->id,
      'email' => $user->email
    ]);
    return $this->json([
      'access_token' => $token,
      'user' => $user
    ], "Mật khẩu đã được đặt lại thành công", 200);
  }

  /**
   * @OA\Get(
   *     path="/v1/auth/verify-reset-token/{token}",
   *     operationId="verifyResetToken",
   *     tags={"Authentication"},
   *     summary="Xác minh token đặt lại mật khẩu",
   *     description="Kiểm tra tính hợp lệ của token đặt lại mật khẩu từ email",
   *     @OA\Parameter(
   *         name="token",
   *         in="path",
   *         required=true,
   *         description="Token đặt lại mật khẩu",
   *         @OA\Schema(type="string", example="550e8400-e29b-41d4-a716-446655440000")
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Token hợp lệ",
   *         @OA\JsonContent(
   *             @OA\Property(property="success", type="boolean", example=true),
   *             @OA\Property(property="message", type="string", example="Mã đặt lại mật khẩu hợp lệ"),
   *             @OA\Property(property="data", type="object",
   *                 @OA\Property(property="email", type="string", example="user@example.com", description="Email của tài khoản"),
   *                 @OA\Property(property="reset_token", type="string", example="550e8400-e29b-41d4-a716-446655440000", description="Token đặt lại mật khẩu")
   *             )
   *         )
   *     ),
   *     @OA\Response(
   *         response=400,
   *         description="Token không hợp lệ hoặc đã hết hạn",
   *         @OA\JsonContent(
   *             @OA\Property(property="success", type="boolean", example=false),
   *             @OA\Property(property="message", type="string", example="Mã đặt lại mật khẩu không hợp lệ hoặc đã hết hạn"),
   *             @OA\Property(property="data", type="null", example=null)
   *         )
   *     )
   * )
   */
  #[Get('/verify-reset-token/{token}', 'auth.verifyResetToken')]
  public function verifyEmailResetToken(string $token)
  {
    $user = User::where('password_reset_code', $token)
      ->where('password_reset_expires_at', '>', now())
      ->first();
    if (!$user) return $this->fail(null, "Mã đặt lại mật khẩu không hợp lệ hoặc đã hết hạn", 400);
    return $this->json([
      'email' => $user->email,
      'reset_token' => $token,
    ], "Mã đặt lại mật khẩu hợp lệ", 200);
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
    /** @var User */
    $user = Auth::user();
    if (!$user->hasVerifiedEmail()) {
      return $this->fail([
        'requires_verification' => true,
      ], "Email chưa được xác minh. Vui lòng kiểm tra email của bạn để xác minh.", 403);
    }
    if ($user->status === UserStatus::SUSPENDED->value) {
      $message = match ($user->status) {
        UserStatus::SUSPENDED->value => "Tài khoản đã bị khóa",
        default => "Tài khoản không trong trạng thái hoạt động",
      };
      return $this->fail(null, $message, 403);
    }
    $user->last_login_at = now();
    $user->save();
    $token = JWTAuth::fromUser($user);
    return $this->json([
      'access_token' => $token,
      'user' => $user,
    ], "Đăng nhập thành công");
  }
}
