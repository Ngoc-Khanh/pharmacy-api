<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AddAddressRequest;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Middleware;

#[Prefix("v2/users")]
#[Middleware("jwt.auth")]
class UserController extends Controller
{
    #[Post("/addAddresses", "users.addAddresses")]
    public function addAddress(AddAddressRequest $request)
    {
        $user = $request->user();
        $newAddress = [
            'address_line1' => $request->address_line1,
            'address_line2' => $request->address_line2 ?? '',
            'city' => $request->city,
            'state' => $request->state ?? '',
            'country' => $request->country,
            'postal_code' => $request->postal_code,
            'is_default' => $request->is_default ?? false,
        ];
        if (!isset($user->addresses) || empty($user->addresses)) {
            $newAddress['is_default'] = true;
        } elseif ($newAddress['is_default']) {
            foreach ($user->addresses as &$address) {
                $address['is_default'] = false;
            }
        }
        $user->addresses = isset($user->addresses) ? array_merge($user->addresses, [$newAddress]) : [$newAddress];
        $user->save();
        return $this->json($user, 'Address added successfully', 201);
    }

    #[Get("/addresses", "users.addresses")]
    public function getAddresses(Request $request)
    {
        $user = $request->user();
        return $this->json($user->addresses, 'Addresses retrieved successfully', 200);
    }
}
