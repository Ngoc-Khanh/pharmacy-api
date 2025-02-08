<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;
use Tymon\JWTAuth\Facades\JWTAuth;

#[Prefix("v2/auth")]
class AuthController extends Controller
{
    #[Post(uri: "/credentials", name: "auth.credentials")]
    public function login(Request $request)
    {
        $request->validate([
            'account' => 'required|string',
            'password' => 'required|string',
        ]);
        $accountField = filter_var($request->input('account'), FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $credentials = [
            $accountField => $request->input('account'),
            'password' => $request->input('password')
        ];
        if (!Auth::attempt($credentials)) return response()->json(['message' => 'Invalid credentials'], 401);
        $user = Auth::user();
        $token = JWTAuth::fromUser($user);
        $result = [
            'access_token' => $token,
            'user' => $user
        ];
        return $this->json($result, "Login successful");
    }
}
