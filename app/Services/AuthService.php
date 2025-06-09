<?php

namespace App\Services;

use App\Events\StoreApprovalRequested;
use App\Events\UserRegistered;
use App\Models\Store;
use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function registerBuyer(array $data): User
    {
        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'],
            'address' => $data['address'],
            'role_enum' => 'buyer',
            'status_enum' => 'active',
        ];

        $user = $this->userRepository->create($userData);
        
        event(new UserRegistered($user));
        
        return $user;
    }

    public function registerSeller(array $data): User
    {
        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'],
            'address' => $data['address'],
            'role_enum' => 'seller',
            'status_enum' => 'pending',
        ];

        $user = $this->userRepository->create($userData);
        
        // Create store
        $store = Store::create([
            'user_id' => $user->id,
            'name' => $data['store_name'],
            'slug' => Str::slug($data['store_name']),
            'description' => $data['store_description'] ?? null,
            'address' => $data['store_address'],
            'phone' => $data['store_phone'],
            'status_enum' => 'pending',
        ]);
        
        event(new StoreApprovalRequested($store));
        event(new UserRegistered($user));
        
        return $user;
    }

    public function approveSellerAccount(User $user): User
    {
        if (!$user->isSeller() || !$user->isPending()) {
            throw new \Exception('User is not a pending seller');
        }

        $user->status_enum = 'active';
        $user->save();
        
        // Also approve the store
        $store = $user->store;
        if ($store && $store->isPending()) {
            $store->status_enum = 'active';
            $store->save();
        }
        
        return $user;
    }

    public function rejectSellerAccount(User $user): User
    {
        if (!$user->isSeller() || !$user->isPending()) {
            throw new \Exception('User is not a pending seller');
        }

        $user->status_enum = 'suspended';
        $user->save();
        
        // Also suspend the store
        $store = $user->store;
        if ($store) {
            $store->status_enum = 'suspended';
            $store->save();
        }
        
        return $user;
    }

    public function suspendUser(User $user): User
    {
        $user->status_enum = 'suspended';
        $user->save();
        
        // If user is a seller, also suspend the store
        if ($user->isSeller()) {
            $store = $user->store;
            if ($store) {
                $store->status_enum = 'suspended';
                $store->save();
            }
        }
        
        return $user;
    }

    public function activateUser(User $user): User
    {
        $user->status_enum = 'active';
        $user->save();
        
        return $user;
    }
}