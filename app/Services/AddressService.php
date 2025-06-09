<?php

namespace App\Services;

use App\Models\Address;
use Illuminate\Support\Facades\Auth;

class AddressService
{
    public function getUserAddresses()
    {
        $user = Auth::user();
        return $user->addresses()->orderBy('is_default', 'desc')->get();
    }

    public function getAddress($id)
    {
        $user = Auth::user();
        return $user->addresses()->findOrFail($id);
    }

    public function createAddress(array $data)
    {
        $user = Auth::user();

        // If this is the first address or is_default is true, set as default
        if ($data['is_default'] ?? false || $user->addresses()->count() === 0) {
            // Remove default flag from other addresses
            $user->addresses()->update(['is_default' => false]);
            $data['is_default'] = true;
        }

        return $user->addresses()->create($data);
    }

    public function updateAddress($id, array $data)
    {
        $user = Auth::user();
        $address = $user->addresses()->findOrFail($id);

        // If setting as default, remove default flag from other addresses
        if ($data['is_default'] ?? false) {
            $user->addresses()->where('id', '!=', $id)->update(['is_default' => false]);
            $data['is_default'] = true;
        }

        $address->update($data);
        return $address;
    }

    public function deleteAddress($id)
    {
        $user = Auth::user();
        $address = $user->addresses()->findOrFail($id);

        // If deleting default address, set another as default if available
        if ($address->is_default) {
            $newDefault = $user->addresses()->where('id', '!=', $id)->first();
            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            }
        }

        return $address->delete();
    }

    public function setDefaultAddress($id)
    {
        $user = Auth::user();

        // Remove default flag from all addresses
        $user->addresses()->update(['is_default' => false]);

        // Set the specified address as default
        $address = $user->addresses()->findOrFail($id);
        $address->update(['is_default' => true]);

        return $address;
    }
}
