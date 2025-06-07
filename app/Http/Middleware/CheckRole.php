<?php

namespace App\Http\Middleware;

use App\Utils\HttpResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Enums\UserRole;

class CheckRole
{
    /**
     * Xử lý phân quyền dựa trên vai trò cho các route.
     * Hỗ trợ kiểm tra role không phân biệt chữ hoa/thường.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles Danh sách các role được phép truy cập
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = Auth::user();
        
        // Kiểm tra user có tồn tại không
        if (!$user) {
            return response()->json([
                HttpResponse::toJson(
                    null,
                    "Bạn cần đăng nhập để truy cập tài nguyên này.",
                    401,
                    request()->getLocale(),
                    'UNAUTHORIZED'
                )
            ], 401);
        }
        
        // Kiểm tra user có thuộc tính role không
        if (!isset($user->role)) {
            return response()->json([
                HttpResponse::toJson(
                    null,
                    "Người dùng không có vai trò hợp lệ.",
                    403,
                    request()->getLocale(),
                    'ROLE_MISSING'
                )
            ], 403);
        }
        
        // Lấy giá trị role của user và chuẩn hóa thành chữ hoa
        $userRoleValue = strtoupper($user->role->value);
        
        // Chuẩn hóa các role được truyền vào thành chữ hoa để so sánh case-insensitive
        $normalizedRoles = array_map('strtoupper', $roles);
        
        // Kiểm tra quyền truy cập
        if (!in_array($userRoleValue, $normalizedRoles)) {
            return response()->json([
                HttpResponse::toJson(
                    null,
                    "Bạn không có quyền truy cập vào tài nguyên này. Yêu cầu một trong các vai trò: " . implode(', ', $roles) . ". Vai trò hiện tại: {$user->role->label()}",
                    403,
                    request()->getLocale(),
                    'ROLE_NOT_ALLOWED'
                )
            ], 403);
        }
        
        return $next($request);
    }
}
