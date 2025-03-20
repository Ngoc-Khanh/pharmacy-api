<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AddAddressRequest;
use MongoDB\BSON\ObjectId;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Patch;
use Spatie\RouteAttributes\Attributes\Delete;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Middleware;

#[Prefix("v2/users")]
#[Middleware("jwt.auth")]
class UserController extends Controller
{
    #[Get("/addresses", "users.addresses")]
    public function getAddresses(Request $request)
    {
        $user = $request->user();
        return $this->json($user->addresses, 'Addresses retrieved successfully', 200);
    }

    #[Post("/add-addresses", "users.addAddresses")]
    public function addAddress(AddAddressRequest $request)
    {
        $user = $request->user();
        $newAddress = [
            'id'            => (string) new ObjectId(),
            'name'          => $request->name,
            'phone'         => $request->phone,
            'address_line1' => $request->address_line1,
            'address_line2' => $request->address_line2 ?? '',
            'city'          => $request->city,
            'state'         => $request->state ?? '',
            'country'       => $request->country,
            'postal_code'   => $request->postal_code,
            'is_default'    => $request->is_default ?? false,
            'created_at'    => now(),
            'updated_at'    => now(),
        ];
        $addresses = $user->addresses ?? [];
        if (!empty($addresses)) {
            foreach ($addresses as $address) {
                if (
                    $address['address_line1'] === $newAddress['address_line1'] &&
                    $address['city'] === $newAddress['city'] &&
                    $address['country'] === $newAddress['country'] &&
                    $address['postal_code'] === $newAddress['postal_code']
                ) {
                    return $this->fail([], 'Address already exists', 400);
                }
            }
        }
        if (empty($addresses)) {
            $newAddress['is_default'] = true;
        } elseif ($newAddress['is_default'] || ($request->has('is_default') && $request->is_default)) {
            $newAddress['is_default'] = true;
            $addresses = array_map(function ($address) {
                $address['is_default'] = false;
                return $address;
            }, $addresses);
        }
        $addresses[] = $newAddress;
        $user->addresses = $addresses;
        $user->save();

        return $this->json($newAddress, 'Address added successfully', 201);
    }

    #[Patch("/update-address/{id}", "users.updateAddress")]
    public function updateAddress(Request $request, string $id)
    {
        $user = $request->user();
        $addresses = $user->addresses ?? [];
        $updatedAddress = null;
        foreach ($addresses as &$address) {
            if ($address['id'] === $id) {
                $address['name'] = $request->name ?? $address['name'];
                $address['phone'] = $request->phone ?? $address['phone'];
                $address['address_line1'] = $request->address_line1 ?? $address['address_line1'];
                $address['address_line2'] = $request->address_line2 ?? $address['address_line2'];
                $address['city'] = $request->city ?? $address['city'];
                $address['state'] = $request->state ?? $address['state'];
                $address['country'] = $request->country ?? $address['country'];
                $address['postal_code'] = $request->postal_code ?? $address['postal_code'];
                $address['updated_at'] = now();
                if ($request->has('is_default') && $request->is_default) {
                    foreach ($addresses as &$addr) {
                        $addr['is_default'] = false;
                    }
                    $address['is_default'] = true;
                }
                $updatedAddress = $address;
                break;
            }
        }
        if (!$updatedAddress) return $this->fail([], 'Address not found', 404);
        $user->addresses = $addresses;
        $user->save();
        return $this->json($updatedAddress, 'Address updated successfully', 200);
    }

    #[Post("/set-default-address/{id}", "users.setDefaultAddress")]
    public function setDefaultAddress(Request $request, string $id)
    {
        $user = $request->user();
        $user->addresses = array_map(function ($address) use ($id) {
            $address['is_default'] = $address['id'] === $id;
            return $address;
        }, $user->addresses);
        $user->save();

        $defaultAddress = collect($user->addresses)->firstWhere('is_default', true);
        return $this->json($defaultAddress, 'Default address set successfully', 200);
    }

    #[Delete("/delete-address/{id}", "users.deleteAddress")]
    public function deleteAddress(Request $request, string $id)
    {
        $user = $request->user();
        $user->addresses = array_filter($user->addresses, function ($address) use ($id) {
            return $address['id'] !== $id;
        });
        $user->save();
        return $this->json([], 'Address deleted successfully', 204);
    }
}
