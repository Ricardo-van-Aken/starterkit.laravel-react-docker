<?php

use Illuminate\Database\Eloquent\Model;
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
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
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