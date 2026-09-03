<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Listing>
 */
class ListingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = fake()->numberBetween(50000, 500000);

        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'title' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'specs' => ['jenis' => 'Test'],
            'photo_path' => null,
            'starting_price' => $price,
            'current_price' => $price,
            'auction_start' => now(),
            'auction_end' => now()->addMinutes(15),
            'status' => 'active',
        ];
    }
}
