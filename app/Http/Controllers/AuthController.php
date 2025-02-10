<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\RouteAttributes\Attributes\Delete;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Put;
use Tymon\JWTAuth\Facades\JWTAuth;

#[Prefix("v2/auth")]
class AuthController extends Controller
{
    #[Get(uri: "/all", name: "auth.all")]
    public function getAllUser()
    {
        $users = User::all();
        return $this->json($users, 'Get all user successful', 200);
    }


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

    #[Post(uri: "/register", name: "auth.register")]
    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|string|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|string',
        ]);
        $user = User::create([
            'username' => $request->input('username'),
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password')),
        ]);

        $token = JWTAuth::fromUser($user);
        $result = [
            'access_token' => $token,
            'user' => $user
        ];
        return $this->json($result, 'Register successful', 201);
    }

    #[Post(uri: "/refreshToken", name: "auth.refreshToken")]
    public function refreshToken(Request $request)
    {
        $token = JWTAuth::getToken();
        if (!$token) {
            return response()->json(['message' => 'Token not provided'], 400);
        }

        try {
            $newToken = JWTAuth::refresh($token);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        $user = JWTAuth::setToken($newToken)->toUser();
        $result = [
            'access_token' => $newToken,
            'user' => $user
        ];
        return $this->json($result, 'Refresh token successful');
    }
}
