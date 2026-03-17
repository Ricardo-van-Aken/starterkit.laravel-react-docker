<?php

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
    ->in('Feature');

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
expect()->extend('toHideId', function () {
    foreach (resolveClasses($this->value) as $class) {
        expect(new $class)->getHidden()
            ->toContain('id');
    }

    return $this;
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

/**
 * Resolves a namespace, class name, or array of class names into a flat array of class names.
 */
function resolveClasses(mixed $value): array
{
    if (is_string($value) && !class_exists($value)) {
        $path = app_path(str_replace(['App\\', '\\'], ['', DIRECTORY_SEPARATOR], $value));
        if (is_dir($path)) {
            return collect(Illuminate\Support\Facades\File::allFiles($path))
                ->map(fn ($file) => $value . '\\' . $file->getFilenameWithoutExtension())
                ->toArray();
        }
    }

    return is_array($value) ? $value : [$value];
}

function something()
{
    // ..
}