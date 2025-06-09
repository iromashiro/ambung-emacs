<?php

namespace App\Repositories\Interfaces;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    public function findById(string $id): ?User;
    
    public function findByEmail(string $email): ?User;
    
    public function findByRoleWithPagination(string $role, int $perPage = 15): LengthAwarePaginator;
    
    public function create(array $data): User;
    
    public function update(string $id, array $data): User;
    
    public function delete(string $id): bool;
}