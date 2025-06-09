<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Store;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create placeholder images directory if it doesn't exist
        if (!Storage::disk('public')->exists('products')) {
            Storage::disk('public')->makeDirectory('products');
        }

        if (!Storage::disk('public')->exists('products/thumbs')) {
            Storage::disk('public')->makeDirectory('products/thumbs');
        }

        // Get all active stores
        $stores = Store::where('status', 'active')->get();

        // Get all categories
        $categories = Category::all();

        // Create products for each store
        foreach ($stores as $store) {
            // Create 5-15 products per store
            $productCount = rand(5, 15);

            for ($i = 0; $i < $productCount; $i++) {
                // Select random category
                $category = $categories->random();

                // Determine if product should be featured (20% chance)
                $isFeatured = rand(1, 5) === 1;

                // Create product
                $product = Product::create([
                    'seller_id' => $store->seller_id,
                    'category_id' => $category->id,
                    'name' => $this->generateProductName($category),
                    'description' => $this->generateProductDescription(),
                    'price' => rand(10000, 1000000) / 100, // Price between 100 and 10,000
                    'stock' => rand(0, 100),
                    'status' => 'active',
                    'is_featured' => $isFeatured,
                ]);

                // Create 1-4 images for each product
                $imageCount = rand(1, 4);
                for ($j = 0; $j < $imageCount; $j++) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'path' => 'products/placeholder-' . rand(1, 10) . '.jpg',
                        'is_primary' => $j === 0, // First image is primary
                        'order' => $j + 1,
                    ]);
                }
            }

            // Create some out-of-stock products (10% of products)
            $outOfStockCount = max(1, round($productCount * 0.1));
            for ($i = 0; $i < $outOfStockCount; $i++) {
                $category = $categories->random();

                $product = Product::create([
                    'seller_id' => $store->seller_id,
                    'category_id' => $category->id,
                    'name' => $this->generateProductName($category),
                    'description' => $this->generateProductDescription(),
                    'price' => rand(10000, 1000000) / 100,
                    'stock' => 0, // Out of stock
                    'status' => 'active',
                    'is_featured' => false,
                ]);

                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => 'products/placeholder-' . rand(1, 10) . '.jpg',
                    'is_primary' => true,
                    'order' => 1,
                ]);
            }
        }
    }

    /**
     * Generate a realistic product name based on category.
     */
    private function generateProductName($category): string
    {
        $adjectives = ['Premium', 'Handmade', 'Organic', 'Traditional', 'Modern', 'Classic', 'Elegant', 'Stylish', 'Unique', 'Authentic'];
        $materials = ['Cotton', 'Leather', 'Wooden', 'Bamboo', 'Batik', 'Silk', 'Ceramic', 'Metal', 'Glass', 'Plastic'];

        $adjective = $adjectives[array_rand($adjectives)];
        $material = $materials[array_rand($materials)];

        // Generate name based on category
        switch ($category->name) {
            case 'Men\'s Clothing':
                $items = ['T-Shirt', 'Shirt', 'Pants', 'Jeans', 'Jacket', 'Hoodie', 'Sweater'];
                return $adjective . ' ' . $material . ' ' . $items[array_rand($items)];

            case 'Women\'s Clothing':
                $items = ['Dress', 'Blouse', 'Skirt', 'Pants', 'Jacket', 'Top', 'Cardigan'];
                return $adjective . ' ' . $material . ' ' . $items[array_rand($items)];

            case 'Smartphones':
                $brands = ['SmartX', 'TechPro', 'MobiMax', 'PhoneGuru', 'CellMaster'];
                $models = ['Pro', 'Ultra', 'Lite', 'Max', 'Plus', 'Note'];
                return $brands[array_rand($brands)] . ' ' . $models[array_rand($models)] . ' ' . rand(5, 15);

            case 'Snacks':
                $types = ['Chips', 'Crackers', 'Cookies', 'Nuts', 'Dried Fruit', 'Candy', 'Chocolate'];
                $flavors = ['Original', 'Spicy', 'Sweet', 'Salty', 'Sour', 'BBQ', 'Cheese'];
                return $adjective . ' ' . $flavors[array_rand($flavors)] . ' ' . $types[array_rand($types)];

            case 'Handmade Crafts':
                $items = ['Bracelet', 'Necklace', 'Earrings', 'Keychain', 'Decoration', 'Ornament', 'Figurine'];
                return $adjective . ' ' . $material . ' ' . $items[array_rand($items)];

            default:
                return $adjective . ' ' . $category->name . ' Item ' . rand(1, 100);
        }
    }

    /**
     * Generate a realistic product description.
     */
    private function generateProductDescription(): string
    {
        $descriptions = [
            "This high-quality product is handcrafted with attention to detail. Perfect for everyday use or as a special gift.",
            "Made from premium materials, this product is designed to last. Its elegant design will complement any style.",
            "Our bestselling product is now available in new variants. Customers love its quality and affordable price.",
            "This unique product is exclusively available in our store. Limited stock available, get yours now!",
            "Crafted by skilled artisans, this product represents the rich cultural heritage of Indonesia.",
            "This product combines traditional craftsmanship with modern design. Functional, beautiful, and sustainable.",
            "A must-have item for your collection. This product offers excellent value for money and superior quality.",
            "Introducing our newest product, designed with your comfort and satisfaction in mind. Try it today!",
            "This versatile product is perfect for multiple occasions. Its timeless design never goes out of style.",
            "Experience the difference with our premium product. We use only the finest materials and production techniques."
        ];

        $features = [
            "• Premium quality materials",
            "• Handcrafted with care",
            "• Environmentally friendly",
            "• Easy to use/wear",
            "• Durable and long-lasting",
            "• Unique design",
            "• Comfortable fit",
            "• Versatile for many occasions",
            "• Makes a perfect gift",
            "• Exclusive to our store"
        ];

        // Shuffle and take 3-5 random features
        shuffle($features);
        $selectedFeatures = array_slice($features, 0, rand(3, 5));
        $featuresList = implode("\n", $selectedFeatures);

        // Select a random description
        $mainDescription = $descriptions[array_rand($descriptions)];

        // Add a second paragraph sometimes
        if (rand(0, 1) === 1) {
            $secondParagraph = $descriptions[array_rand($descriptions)];
            $mainDescription .= "\n\n" . $secondParagraph;
        }

        // Combine description and features
        return $mainDescription . "\n\n" . $featuresList;
    }
}
