<?php
// php artisan test --filter=PasswordConfirmationTest

use App\Models\User;

test('confirm password screen can be rendered', function () {
    $user = User::factory()->create();

    $builder = $this->httpRequestBuilder()->actingAs($user);
    $response = $builder->get('/confirm-password')->send();

    $this->assertEquals(200, $response->getStatusCode());
});

test('password confirmation middleware redirects to the correct page', function() {
    $user = User::factory()->create();

    // Log in as the user
    $builder = $this->httpRequestBuilder()->actingAs($user);

    // Visit '/requires-password-confirmation' page.
    $response = $builder->get('/requires-password-confirmation')->send();

    // Test 1: Check if user gets redirected to '/confirm-password'
    $this->assertEquals(302, $response->getStatusCode());
    $this->assertStringContainsString('/confirm-password', $response->getHeaderLine('Location'));
});

test('password can be confirmed', function () {
    $user = User::factory()->create();

    // Log in as the user
    $builder = $this->httpRequestBuilder()->actingAs($user);

    // Visit '/confirm-password' page.
    $builder->get('/requires-password-confirmation')->send();

    // Try to confirm with correct password
    $response = $builder->post('/confirm-password', [
        'password' => 'password',
    ])->send();

    // Test 1: Check if user gets redirected to the protected route
    $this->assertEquals(302, $response->getStatusCode());
    $this->assertStringContainsString('/requires-password-confirmation', $response->getHeaderLine('Location'));

    // Test 2: Check if protected route is accessible
    $response = $builder->get('/requires-password-confirmation')->send();
    $this->assertEquals(200, $response->getStatusCode());
});

test('password is not confirmed with invalid password', function () {
    $user = User::factory()->create();

    // Log in as the user
    $builder = $this->httpRequestBuilder()->actingAs($user);

    // Visit '/confirm-password' page.
    $builder->get('/confirm-password')->send();

    // Try to confirm with wrong password
    $response = $builder->post('/confirm-password', [
        'password' => 'wrong-password',
    ])->send();

    // Test 1: Check if user gets redirected back to '/confirm-password'
    $this->assertEquals(302, $response->getStatusCode());
    $this->assertStringContainsString('/confirm-password', $response->getHeaderLine('Location'));

    // Test 2: Check if protected route is not accessible
    $response = $builder->get('/requires-password-confirmation')->send();
    $this->assertEquals(302, $response->getStatusCode());
    $this->assertStringContainsString('/confirm-password', $response->getHeaderLine('Location'));
}); 