<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

class CategoryService
{
    protected $categoryRepository;

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Get all active categories
     *
     * @return Collection
     */
    public function getAllCategories(): Collection
    {
        return Cache::remember('categories.all', 3600, function () {
            return $this->categoryRepository->getAllCategories();
        });
    }

    /**
     * Get category by ID
     *
     * @param int $id
     * @return Category|null
     */
    public function getCategoryById(int $id): ?Category
    {
        return $this->categoryRepository->findById($id);
    }

    /**
     * Get category by slug
     *
     * @param string $slug
     * @return Category|null
     */
    public function getCategoryBySlug(string $slug): ?Category
    {
        return $this->categoryRepository->findBySlug($slug);
    }

    /**
     * Get subcategories of a parent category
     *
     * @param int $parentId
     * @return Collection
     */
    public function getSubcategories(int $parentId): Collection
    {
        return Cache::remember("categories.subcategories.{$parentId}", 3600, function () use ($parentId) {
            return $this->categoryRepository->getSubcategories($parentId);
        });
    }

    /**
     * Create a new category
     *
     * @param array $data
     * @return Category
     */
    public function createCategory(array $data): Category
    {
        $category = $this->categoryRepository->create($data);
        $this->clearCategoryCache();
        return $category;
    }

    /**
     * Update an existing category
     *
     * @param Category $category
     * @param array $data
     * @return bool
     */
    public function updateCategory(Category $category, array $data): bool
    {
        $result = $this->categoryRepository->update($category, $data);
        $this->clearCategoryCache();
        return $result;
    }

    /**
     * Delete a category
     *
     * @param Category $category
     * @return bool
     */
    public function deleteCategory(Category $category): bool
    {
        $result = $this->categoryRepository->delete($category);
        $this->clearCategoryCache();
        return $result;
    }

    /**
     * Clear category cache
     */
    private function clearCategoryCache(): void
    {
        Cache::forget('categories.all');
        Cache::forget('categories.with.products');

        // Clear subcategory caches
        $categories = $this->categoryRepository->getAllCategories();
        foreach ($categories as $category) {
            Cache::forget("categories.subcategories.{$category->id}");
        }
    }
}
