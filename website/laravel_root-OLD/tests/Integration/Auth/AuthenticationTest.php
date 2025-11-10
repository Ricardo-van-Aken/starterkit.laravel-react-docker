<?php
// php artisan test --filter=AuthenticationTest

use App\Models\User;

test('login screen can be rendered', function () {
    $response = $this->client->get('/login');

    $this->assertEquals(200, $response->getStatusCode());
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    sleep(10);

    // Login
    $builder = $this->httpRequestBuilder()->withXsrf()->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);
    $response = $builder->send();

    // Now try to access a protected route
    $response = $builder->get('/dashboard')->send();

    $this->assertEquals(200, $response->getStatusCode());
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    // Failed login
    $builder = $this->httpRequestBuilder()->withXsrf()->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);
    $builder->send();

    // Now try to access a protected route
    $response = $builder->get('/requires-auth')->send();

    // Check if user gets redirected to login
    $this->assertEquals(302, $response->getStatusCode());
    $this->assertStringContainsString('/login', $response->getHeaderLine('Location'));
});

test('users can logout', function () {
    $user = User::factory()->create();

    // Logout the user
    $builder = $this->httpRequestBuilder()->actingAs($user)->post('/logout', []);
    $builder->send();

    // Now try to access a protected route
    $response = $builder->get('/requires-auth')->send();

    // Check if user gets redirected to login
    $this->assertEquals(302, $response->getStatusCode());
    $this->assertStringContainsString('/login', $response->getHeaderLine('Location'));
});
