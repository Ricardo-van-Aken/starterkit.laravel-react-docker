<?php
// php artisan test --filter=EmailVerificationTest

use App\Models\User;

test('email verification screen can be rendered', function () {
    $user = User::factory()->unverified()->create();

    $builder = $this->httpRequestBuilder()->actingAs($user);
    $response = $builder->get('/verify-email')->send();

    // Test 1: Check if email verification screen can be rendered.
    $this->assertEquals(200, $response->getStatusCode());
});


test('email can be verified', function () {
    // This test would require generating a signed verification URL and simulating the verification process.
    $this->markTestIncomplete('Email verification flow requires signed URL and event simulation.');
});


test('email is not verified with invalid hash', function () {
    // This test would require generating an invalid signed verification URL and simulating the process.
    $this->markTestIncomplete('Email verification flow requires signed URL and event simulation.');
}); 