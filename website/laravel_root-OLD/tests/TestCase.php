<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected static $migrated = false;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure tests only run in testing environment (.env.testing or phpunit.xml is set)
        if (config('app.env') !== 'testing') {
            $this->markTestSkipped('Tests can only run in testing environments, as they truncate the main 
                                    database. Current environment: ' . config('app.env'));
        }

        // Ensure tests don't use the main db connection
        if (config('database.default') !== 'mysql_testing') {
            $this->markTestSkipped('Tests must use the mysql_testing database connection. Current connection: ' . config('database.default'));
        }

        // Set up the database by migrating it once for all tests.
        if (!static::$migrated) {
            \Illuminate\Support\Facades\Artisan::call('migrate');
            static::$migrated = true;
        }
    }
}
