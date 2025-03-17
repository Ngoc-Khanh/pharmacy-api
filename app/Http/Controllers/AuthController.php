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

#[Prefix("v2/auth")]
class AuthController extends Controller
{
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

    #[Post("/credentials", "auth.credentials")]
    public function credentials(Request $request)
    {
        $validator = Validator::make($request->only(['account', 'password']), [
            'account' => 'required|string',
            'password' => 'required|string',
        ]);
        if ($validator->fails()) return $this->fail([], $validator->errors()->first(), 422);
        $accountField = filter_var($request->input('account'), FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $credentials = $request->only(['password']);
        $credentials[$accountField] = $request->input('account');
        if (!Auth::attempt($credentials)) return $this->fail([], 'Invalid credentials', 401);
        $user = Auth::user();
        $user->last_login_at = Carbon::now();
        $token = JWTAuth::fromUser($user);
        return $this->json([
            'access_token' => $token,
            'user' => $user,
        ], 'Login successful');
    }

    #[Get("/me", "auth.me", "jwt.auth")]
    public function me(Request $request)
    {
        return $this->json($request->user(), "User profile retrieved successfully");
    }
}
