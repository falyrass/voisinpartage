<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    protected $model = Article::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->words(2, true),
            'description' => $this->faker->sentence(10),
            'status' => 'published',
            'lat' => $this->faker->randomFloat(7, 45.45, 45.55),
            'lon' => $this->faker->randomFloat(7, -73.60, -73.50),
        ];
    }
}
