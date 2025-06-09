<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create main categories
        $mainCategories = [
            [
                'name' => 'Fashion',
                'description' => 'Clothing, shoes, and accessories',
                'icon' => 'fa-tshirt',
                'order' => 1,
            ],
            [
                'name' => 'Electronics',
                'description' => 'Gadgets, phones, and electronic devices',
                'icon' => 'fa-mobile-alt',
                'order' => 2,
            ],
            [
                'name' => 'Home & Living',
                'description' => 'Furniture, decor, and household items',
                'icon' => 'fa-home',
                'order' => 3,
            ],
            [
                'name' => 'Health & Beauty',
                'description' => 'Cosmetics, personal care, and wellness products',
                'icon' => 'fa-heart',
                'order' => 4,
            ],
            [
                'name' => 'Food & Beverages',
                'description' => 'Groceries, snacks, and drinks',
                'icon' => 'fa-utensils',
                'order' => 5,
            ],
            [
                'name' => 'Handmade Crafts',
                'description' => 'Artisanal and handcrafted products',
                'icon' => 'fa-paint-brush',
                'order' => 6,
            ],
        ];

        foreach ($mainCategories as $category) {
            Category::create($category);
        }

        // Create subcategories for Fashion
        $fashionCategory = Category::where('name', 'Fashion')->first();
        $fashionSubcategories = [
            'Men\'s Clothing',
            'Women\'s Clothing',
            'Kids\' Clothing',
            'Shoes',
            'Bags & Accessories',
            'Traditional Wear',
        ];

        foreach ($fashionSubcategories as $index => $name) {
            Category::create([
                'name' => $name,
                'description' => 'Subcategory of Fashion',
                'parent_id' => $fashionCategory->id,
                'order' => $index + 1,
            ]);
        }

        // Create subcategories for Electronics
        $electronicsCategory = Category::where('name', 'Electronics')->first();
        $electronicsSubcategories = [
            'Smartphones',
            'Laptops & Computers',
            'Audio & Headphones',
            'Cameras',
            'Accessories',
        ];

        foreach ($electronicsSubcategories as $index => $name) {
            Category::create([
                'name' => $name,
                'description' => 'Subcategory of Electronics',
                'parent_id' => $electronicsCategory->id,
                'order' => $index + 1,
            ]);
        }

        // Create subcategories for Home & Living
        $homeCategory = Category::where('name', 'Home & Living')->first();
        $homeSubcategories = [
            'Furniture',
            'Kitchen & Dining',
            'Bedding',
            'Decor',
            'Storage & Organization',
        ];

        foreach ($homeSubcategories as $index => $name) {
            Category::create([
                'name' => $name,
                'description' => 'Subcategory of Home & Living',
                'parent_id' => $homeCategory->id,
                'order' => $index + 1,
            ]);
        }

        // Create subcategories for Food & Beverages
        $foodCategory = Category::where('name', 'Food & Beverages')->first();
        $foodSubcategories = [
            'Snacks',
            'Beverages',
            'Baked Goods',
            'Traditional Foods',
            'Healthy Foods',
        ];

        foreach ($foodSubcategories as $index => $name) {
            Category::create([
                'name' => $name,
                'description' => 'Subcategory of Food & Beverages',
                'parent_id' => $foodCategory->id,
                'order' => $index + 1,
            ]);
        }
    }
}