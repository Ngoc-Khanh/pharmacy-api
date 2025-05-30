<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use function Laravel\Prompts\error;

class EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->hasVerifiedEmail()) {
            // Nếu người dùng chưa xác minh email, trả về lỗi 403
            return response()->json([
                'data' => null,
                'message' => 'Email chưa được xác minh. Vui lòng kiểm tra email của bạn để xác minh.',
                'status' => 403,
                'locale' => 'vi',
                'error' => 'EmailNotVerified'
            ]);
        }
        return $next($request);
    }
}
