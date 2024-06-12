<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Group;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Post::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'group_id' => Group::factory(),
            'title' => substr($this->faker->title(), 0, 50),
            'description' => substr($this->faker->description(), 0, 200),
            'img_url' => 'https://i.pinimg.com/564x/25/ee/de/25eedef494e9b4ce02b14990c9b5db2d.jpg',
            'status' => 'public',
            'is_highlighted' => false,
        ];
    }
}
