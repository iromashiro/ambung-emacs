<?php

namespace App\Repositories\Eloquent;

use App\Models\Category;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function findById(int $id): ?Category
    {
        return Category::find($id);
    }

    public function findBySlug(string $slug): ?Category
    {
        return Category::where('slug', $slug)
            ->where('is_active', true)
            ->first();
    }

    public function getAllCategories(): Collection
    {
        return Category::where('is_active', true)
            ->orderBy('order')
            ->get();
    }

    public function getSubcategories(int $parentId): Collection
    {
        return Category::where('parent_id', $parentId)
            ->where('is_active', true)
            ->orderBy('order')
            ->get();
    }

    public function create(array $data): Category
    {
        return Category::create($data);
    }

    public function update(Category $category, array $data): bool
    {
        return $category->update($data);
    }

    public function delete(Category $category): bool
    {
        return $category->delete();
    }
}
