<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'post_id' => Post::factory(),
            'comment' => substr($this->faker->comment(), 0, 200),
            'reply_cmt_id' => Comment::factory(), 
        ];
    }

    /**
     * Indicate that the comment is a reply to another comment.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function reply(Comment $replyToComment): Factory
    {
        return $this->state([
            'reply_cmt_id' => $replyToComment->id,
        ]);
    }
}
