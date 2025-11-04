<?php
// php artisan test --filter=ProfileTest

use App\Models\User;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this->httpRequestBuilder()->actingAs($user)->get('/profile')->send();

    $this->assertEquals(200, $response->getStatusCode());
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $builder = $this->httpRequestBuilder()->actingAs($user);
    $response = $builder->patch('/profile', [
        'name' => 'Test User',
        'email' => 'test@example.com',
    ])->send();

    // Test 1: Check if user is redirected to profile page.
    $this->assertEquals(302, $response->getStatusCode());
    $this->assertStringContainsString('/profile', $response->getHeaderLine('Location'));

    // Test 2: Check if user's new profile information is saved.
    $user->refresh();
    $this->assertSame('Test User', $user->name);
    $this->assertSame('test@example.com', $user->email);

    // Test 3: Check if email is unverified.
    $this->assertNull($user->email_verified_at);
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $emailVerifiedAt = $user->email_verified_at;

    $builder = $this->httpRequestBuilder()->actingAs($user);
    $response = $builder->patch('/profile', [
        'name' => 'Test User',
        'email' => $user->email,
    ])->send();

    // Test 1: Check if user is redirected to profile page.
    $this->assertEquals(302, $response->getStatusCode());
    $this->assertStringContainsString('/profile', $response->getHeaderLine('Location'));

    // Test 2: Check if user's new profile information is saved.
    $user->refresh();
    $this->assertSame('Test User', $user->name);

    // Test 3: Check if email is verification stays consistent.
    $this->assertEquals($emailVerifiedAt, $user->email_verified_at);
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $builder = $this->httpRequestBuilder()->actingAs($user);
    $response = $builder->delete('/profile', [
        'password' => 'password',
    ])->send();

    // Test 1: Check if user is redirected to home page.
    $this->assertEquals(302, $response->getStatusCode());
    $this->assertStringContainsString('/', $response->getHeaderLine('Location'));

    // Test 2: Check if user is deleted.
    $this->assertNull(User::find($user->id));

    // Test 3: Check if user is unauthenticated (should be implied if the user is deleted).
    // $builder->get('/login')->send();
    $response = $builder->get('/requires-auth')->send();
    $this->assertEquals(302, $response->getStatusCode());
    $this->assertStringContainsString('/login', $response->getHeaderLine('Location'));

    // Test 4: Check if user can not log in (should be implied if the user is deleted).
    $response = $builder->withXsrf()->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->send();
    $response = $builder->get('/requires-auth')->send();
    $this->assertEquals(302, $response->getStatusCode());
    $this->assertStringContainsString('/login', $response->getHeaderLine('Location'));
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $builder = $this->httpRequestBuilder()->actingAs($user);

    $builder->get('/profile')->send();

    $response = $builder->delete('/profile', [
        'password' => 'wrong-password',
    ])->send();

    // Test 1: Check if user is redirected to last (currently /profile) page.
    $this->assertEquals(302, $response->getStatusCode());
    $this->assertStringContainsString('/profile', $response->getHeaderLine('Location'));

    // Test 2: Check if user is not deleted.
    $this->assertNotNull(User::find($user->id));

    // Test 3: Check if user is authenticated.
    $response = $builder->get('/requires-auth')->send();
    $this->assertEquals(200, $response->getStatusCode());

    // Log out.
    $builder->post('/logout')->send();
    // Check if user is unauthenticated.
    $response = $builder->get('/requires-auth')->send();
    $this->assertEquals(302, $response->getStatusCode());
    $this->assertStringContainsString('/login', $response->getHeaderLine('Location'));

    // Test 4: Check if user can authenticate.
    $builder->withXsrf()->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->send();
    $response = $builder->get('/requires-auth')->send();
    $this->assertEquals(200, $response->getStatusCode());
});
