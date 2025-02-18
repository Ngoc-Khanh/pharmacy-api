<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Put;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Patch;

#[Prefix("v2/account")]
#[Middleware("jwt.auth")]
class UserController extends Controller
{
    #[Get("/all", "auth.all")]
    public function getAllUser()
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return $this->json(['message' => 'Unauthorized'], 401);
        }
        return $this->json(User::all(), 'Get all user successful', 200);
    }

    #[Patch("/update-account", "account.update-account")]
    public function updateAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'email|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'firstname' => 'string|max:255',
            'lastname' => 'string|max:255',
            'phone' => 'string|max:15',
            'address' => 'string|max:255',
        ]);
        if ($validator->fails()) return $this->fail([], $validator->errors(), 422,);
        $user = Auth::user();
        $user->update($request->only([
            'email',
            'phone',
            'address',
            'lastname',
            'firstname',
            'status',
            'avatar'
        ]));
        return $this->json($user, 'Update account successful');
    }
}
