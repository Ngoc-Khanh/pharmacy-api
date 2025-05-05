<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

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
        if (!$user || !in_array($user->role, $roles)) {
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
