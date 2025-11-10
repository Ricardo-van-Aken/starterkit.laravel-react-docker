<?php
// php artisan test --filter=PasswordUpdateTest

use App\Models\User;

test('password can be updated', function () {
    $user = User::factory()->create();

    $builder = $this->httpRequestBuilder()->actingAs($user);

    // Visit '/profile' page, which is now the last visited page.
    $response = $builder->get('/profile')->send();
    $this->assertEquals(200, $response->getStatusCode());

    // Update password
    $response = $builder->put('/password', [
        'current_password' => 'password',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ])->send();

    // Test 1: Check if user is unauthenticated by trying to access protected route.
    $response = $builder->get('/requires-auth')->send();
    $this->assertEquals(302, $response->getStatusCode());
    $this->assertStringContainsString('/login', $response->getHeaderLine('Location'));

    // Login with old (wrong) password.
    $builder->actingAs($user, 'password');

    // Test 2: Check if user is still unauthenticated by trying to access protected route.
    $response = $builder->get('/requires-auth')->send();
    $this->assertEquals(302, $response->getStatusCode());
    $this->assertStringContainsString('/login', $response->getHeaderLine('Location'));

    // Login with new (correct) password.
    $builder->actingAs($user, 'new-password');

    // Test 3: Check if user is authenticated and can access protected route.
    $response = $builder->get('/requires-auth')->send();
    $this->assertEquals(200, $response->getStatusCode());
});

test('correct password must be provided to update password', function () {
    $user = User::factory()->create();

    $builder = $this->httpRequestBuilder()->actingAs($user);

    // Visit '/profile' page, which is now the last visited page.
    $response = $builder->get('/profile')->send();
    $this->assertEquals(200, $response->getStatusCode());

    $response = $builder->put('/password', [
        'current_password' => 'wrong-password',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ])->send();

    // Test 1: Check if user gets redirected to last visited page.
    $this->assertEquals(302, $response->getStatusCode());
    $this->assertStringContainsString('/profile', $response->getHeaderLine('Location'));

    // Test 2: Check if user is still authenticated.
    $response = $builder->get('/requires-auth')->send();
    $this->assertEquals(200, $response->getStatusCode());

    // Log out.
    $builder->post('/logout')->send();
    // Check if user is unauthenticated.
    $response = $builder->get('/requires-auth')->send();
    $this->assertEquals(302, $response->getStatusCode());
    $this->assertStringContainsString('/login', $response->getHeaderLine('Location'));

    // Test 3: Check if login with new (wrong) password fails.
    $builder->actingAs($user, 'new-password');
    $response = $builder->get('/requires-auth')->send();
    $this->assertEquals(302, $response->getStatusCode());
    $this->assertStringContainsString('/login', $response->getHeaderLine('Location'));

    // Test 4: Check if login with old (correct) password works.
    $builder->actingAs($user, 'password');
    $response = $builder->get('/requires-auth')->send();
    $this->assertEquals(200, $response->getStatusCode());
}); 