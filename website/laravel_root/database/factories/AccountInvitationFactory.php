<?php

namespace Database\Factories;

use App\Models\AccountInvitation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AccountInvitation>
 */
class AccountInvitationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => $this->faker->unique()->safeEmail(),
            'claim_token' => Str::random(60),
            'expires_at' => now()->addDays(7),
        ];
    }
}
