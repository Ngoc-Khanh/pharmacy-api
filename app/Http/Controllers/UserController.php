<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;

#[Prefix("v2/users")]
#[Middleware("jwt.auth")]
class UserController extends Controller
{
    #[Post("/addAddresses", "users.addAddresses")]
    public function addAddresses(Request $request)
    {
        $userId = $request->user()->id;
        $validator = Validator::make($request->all(), [
            'addressLine1' => 'required|string',
            'addressLine2' => 'nullable|string',
            'city'         => 'required|string',
            'state'        => 'nullable|string',
            'country'      => 'required|string',
            'postalCode'   => 'required|string',
            'isDefault'    => 'nullable|boolean'
        ]);
        if ($validator->fails()) return $this->fail([], $validator->errors(), 422);
        $user = User::find($userId);
        if (!$user) return $this->fail([], "User not found", 404);
        $newAddress = [
            'addressLine1' => $request->addressLine1,
            'addressLine2' => $request->addressLine2,
            'city'         => $request->city,
            'state'        => $request->state,
            'country'      => $request->country,
            'postalCode'   => $request->postalCode,
            'isDefault'    => $request->has('isDefault') ? (bool)$request->isDefault : false
        ];
        if ($newAddress['isDefault']) {
            if (isset($user->addresses) && is_array($user->addresses)) {
                foreach ($user->addresses as &$address) {
                    $address['isDefault'] = false;
                }
            }
        } else {
            if (empty($user->addresses)) {
                $newAddress['isDefault'] = true;
            }
        }
        $addresses = $user->addresses ?? [];
        $addresses[] = $newAddress;
        $user->addresses = $addresses;
        $user->updated_at = Carbon::now();
        $user->save();
        return $this->json($newAddress, "Address added successfully", 201);
    }
}
