<?php

namespace App\Repositories\Interfaces;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

interface CategoryRepositoryInterface
{
    public function findById(int $id): ?Category;

    public function findBySlug(string $slug): ?Category;

    public function getAllCategories(): Collection;

    public function getSubcategories(int $parentId): Collection;

    public function create(array $data): Category;

    public function update(Category $category, array $data): bool;

    public function delete(Category $category): bool;
}
