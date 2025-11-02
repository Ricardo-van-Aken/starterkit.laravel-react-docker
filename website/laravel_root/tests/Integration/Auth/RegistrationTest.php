<?php
// php artisan test --filter=RegistrationTest

test('registration screen can be rendered', function () {
    $response = $this->httpRequestBuilder()->get('/register')();

    $this->assertEquals(200, $response->getStatusCode());
});

test('new users can register', function () {
    $builder = $this->httpRequestBuilder();

    $email = 'integration_' . uniqid() . '@example.com';

    // Register new user.
    $response = $builder->withXsrf()->post('/register', [
        'name' => 'Test User',
        'email' => $email,
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->send();

    // Test 1: Check if user is redirected to dashboard.
    $this->assertEquals(302, $response->getStatusCode());
    $this->assertStringContainsString('/dashboard', $response->getHeaderLine('Location'));

    // Test 2: Check if user is authenticated.
    $response = $builder->get('/requires-auth')->send();
    $this->assertEquals(200, $response->getStatusCode());

    // Log out.
    $response = $builder->post('/logout')->send();
    // Check if user is unauthenticated.
    $response = $builder->get('/requires-auth')->send();
    $this->assertEquals(302, $response->getStatusCode());
    $this->assertStringContainsString('/login', $response->getHeaderLine('Location'));

    // Test 3: Check if user can log in.
    $response = $builder->withXsrf()->post('/login', [
        'email' => $email,
        'password' => 'password',
    ])->send();
    // Check if user is authenticated.
    $response = $builder->get('/requires-auth')->send();
    $this->assertEquals(200, $response->getStatusCode());
});