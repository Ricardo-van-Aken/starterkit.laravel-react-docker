<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RequiredDataSeeder extends Seeder
{
    /**
     * Run the database queries that contain essential production data.
     */
    public function run(): void
    {
        $this->call(DefaultRolesAndPermissionsSeeder::class);
    }
}
