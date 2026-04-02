<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;
use Pest\Arch\Contracts\ArchExpectation;
use PHPUnit\Architecture\Elements\ObjectDescription;
use Pest\Arch\Support\FileLineFinder;
use Pest\Arch\Expectations\Targeted;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature')
    ->beforeEach(function () {
        $this->seed(\Database\Seeders\RequiredDataSeeder::class);
    })
    ->afterEach(function () {
        // Clear the sandboxed Redis keys belonging to THIS parallel test process 
        // by identifying active connections used by Cache, Session, and Queue.
        $connections = [];
        if (config('cache.default') === 'redis') {
            $connections[] = config('cache.stores.redis.connection', 'default');
        }
        if (config('session.driver') === 'redis') {
            $connections[] = config('session.connection', 'default');
        }
        if (config('queue.default') === 'redis') {
            $connections[] = config('queue.connections.redis.connection', 'default');
        }

        $prefix = config('database.redis.options.prefix', '');
        $prefixLength = strlen($prefix);

        foreach (array_unique($connections) as $connection) {
            $redis = Redis::connection($connection);
            
            // Note: `$redis->keys('*')` automatically takes only the keys with the prefix defined in
            // config('database.redis.options.prefix', '')
            if ($keys = $redis->keys('*')) {

                // Remove prefix from all redis keys to get the actual key
                if ($prefixLength > 0) {
                    $keys = array_map(function ($key) use ($prefixLength) {
                        return substr($key, $prefixLength);
                    }, $keys);
                }

                // Remove all keys from the redis connection
                $redis->del(...$keys);
            }
        }
    });

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/**
 * Custom expectation to check if a model (or all models in a namespace) hides the 'id' field.
 * This bridges the gap between Arch tests (static) and Runtime properties.
 */
expect()->extend('toHideParams', function (string|array $params): ArchExpectation {
    $params = (array) $params;

    return Targeted::make(
        $this,
        function (ObjectDescription $object) use ($params): bool {
            // make sure class exists
            if (! class_exists($object->name)) {
                return false;
            }

            $reflection = $object->reflectionClass ?? new \ReflectionClass($object->name);

            // only apply to Eloquent models
            if (! $reflection->isSubclassOf(Model::class)) {
                return false;
            }

            // instantiate model safely without triggering constructor
            $model = $reflection->newInstanceWithoutConstructor();

            $hidden = $model->getHidden();
            $visible = $model->getVisible();

            foreach ($params as $param) {
                $isHidden = ! empty($visible)
                    ? ! in_array($param, $visible, true)
                    : in_array($param, $hidden, true);

                if (! $isHidden) {
                    return false;
                }
            }

            return true;
        },
        'to hide params [' . implode(', ', $params) . ']',
        FileLineFinder::where(fn (string $line): bool => true),
    );
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}