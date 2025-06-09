<?php

namespace Database\Factories;

use App\Models\Wishlist;
use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class WishlistFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Wishlist::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::where('role', 'buyer')->inRandomOrder()->first()?->id ?? User::factory()->create()->id,
            'product_id' => Product::where('status', 'active')->inRandomOrder()->first()?->id ?? Product::factory()->create()->id,
        ];
    }
}