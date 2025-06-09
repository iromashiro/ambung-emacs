<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class CartFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Cart::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::where('role', 'buyer')->inRandomOrder()->first()?->id ?? User::factory()->create()->id,
            'product_id' => Product::where('status', 'active')->where('stock', '>', 0)->inRandomOrder()->first()?->id ?? Product::factory()->create()->id,
            'quantity' => $this->faker->numberBetween(1, 5),
        ];
    }
}