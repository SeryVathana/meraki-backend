<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Group;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GroupInvite>
 */
class GroupInviteFactory extends Factory
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
            'group_id' => Group::factory(),
            'accepted_at' => null,
        ];
    }
    public function accepted(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'accepted_at' => Carbon::now(),
            ];
        });
    }
}
