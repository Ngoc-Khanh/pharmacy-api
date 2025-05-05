<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Enums\UserRole;

class CheckRole
{
    /**
     * Xử lý phân quyền dựa trên vai trò cho các route.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     * @return \Symfony\Component\HttpFoundation\Response
     */

    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        
        // Kiểm tra user có tồn tại không
        if (!$user) {
            return response()->json([
                'errors' => [[
                    'status' => '401',
                    'code' => 'UNAUTHORIZED',
                    'title' => 'Chưa xác thực',
                    'detail' => 'Bạn cần đăng nhập để truy cập tài nguyên này.'
                ]]
            ], 401);
        }
        
        // So sánh giá trị của Enum (chuỗi) thay vì so sánh đối tượng Enum
        $userRoleValue = $user->role->value;
        
        if (!in_array($userRoleValue, $roles)) {
            return response()->json([
                'errors' => [[
                    'status' => '403',
                    'code' => 'ROLE_FORBIDDEN',
                    'title' => 'Truy cập bị từ chối',
                    'detail' => 'Bạn không có quyền truy cập vào tài nguyên này.'
                ]]
            ], 403);
        }
        
        return $next($request);
    }
}
