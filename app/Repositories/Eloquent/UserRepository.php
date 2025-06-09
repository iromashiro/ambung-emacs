<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository implements UserRepositoryInterface
{
    public function findById(string $id): ?User
    {
        return User::find($id);
    }
    
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }
    
    public function findByRoleWithPagination(string $role, int $perPage = 15): LengthAwarePaginator
    {
        return User::where('role_enum', $role)
                  ->orderBy('name')
                  ->paginate($perPage);
    }
    
    public function create(array $data): User
    {
        return User::create($data);
    }
    
    public function update(string $id, array $data): User
    {
        $user = User::findOrFail($id);
        $user->update($data);
        return $user;
    }
    
    public function delete(string $id): bool
    {
        return User::destroy($id) > 0;
    }
}