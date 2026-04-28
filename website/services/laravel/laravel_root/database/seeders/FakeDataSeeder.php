<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class FakeDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * This seeder is intended ONLY for local development and testing.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => 'password',
                'max_tenants' => 5,
                'email_verified_at' => now(),
            ]
        );

        // User::factory(10)->create();
    }
}
