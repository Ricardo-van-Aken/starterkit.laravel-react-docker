<?php

namespace Database\Factories;

use App\Models\TenantInvitation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TenantInvitation>
 */
class TenantInvitationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => \App\Models\Tenant::factory(),
            'email' => fake()->unique()->safeEmail(),
            'status' => \App\Enums\TenantInvitationStatus::Pending,
            'roles' => [],
            'permissions' => [],
            'accept_token' => \Illuminate\Support\Str::random(64),
            'decline_token' => \Illuminate\Support\Str::random(64),
            'expires_at' => now()->addDays(7),
        ];
    }
}
