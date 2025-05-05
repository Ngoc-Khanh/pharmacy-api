<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;
use Tymon\JWTAuth\Facades\JWTAuth;

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
      'status' => UserStatus::ACTIVE->value,
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
}
