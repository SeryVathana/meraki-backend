<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => substr($this->faker()->first_name(), 0, 50),
            'last_name' => substr($this->faker()->last_name(), 0, 50),
            'email' => substr($this->faker()->unique()->safeEmail(), 0, 100),
            'password' => static::$password ??= Hash::make('password'),
            'role' => "admin",
            'email_verified_at' => Carbon::now(),
            'pf_img_url' => "https://i.pinimg.com/736x/2f/21/94/2f21940ee0948af25337e339d4899c36.jpg",
            'social_login_info' => json_encode(['id' => 1, 'token' => 'asdasdasd']),
            'followers' => json_encode([1, 2, 3]),
            'followings' => json_encode([1, 2, 3]),
            'remember_token' => Str::random(10),
        ];
    }
    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
