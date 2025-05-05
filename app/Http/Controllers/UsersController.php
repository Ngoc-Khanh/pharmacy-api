<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Prefix;

/**
 * @OA\Tag(
 *     name="Users",
 *     description="Các API quản lý người dùng"
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
#[Prefix("v1/admin/users")]
#[Middleware(["jwt.auth", "role:admin,pharmacist"])]
class UsersController extends Controller
{
  #[Get("/", name: "admin.users.index")]
  public function index(Request $request)
  { 
    $user = $request->user()->all();
    return $this->json($user, "Lấy danh sách người dùng thành công");
  }
}
