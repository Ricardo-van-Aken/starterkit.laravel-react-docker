<?php

use App\Models\AccountInvitation;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;

test('user can view the take ownership page with a valid token', function () {
    /* --- Setup --- */
    $invitation = AccountInvitation::create([
        'email' => 'test@example.com',
        'claim_token' => Str::random(32),
        'expires_at' => now()->addDays(7),
    ]);

    /* --- Request --- */
    $url = URL::signedRoute('invitation.edit', ['token' => $invitation->claim_token]);
    $response = $this->get($url);

    /* --- Assert HTTP response status --- */
    expect($response->status())->toBe(200);
});

test('user cannot view the take ownership page with an invalid token', function () {
    /* --- Request --- */
    $url = URL::signedRoute('invitation.edit', ['token' => 'invalid-token']);
    $response = $this->get($url);

    /* --- Assert HTTP response status --- */
    expect($response->status())->toBe(404);
});

test('user can claim their account with a valid token and password', function () {
    /* --- Setup --- */
    $invitation = AccountInvitation::create([
        'email' => 'claim@example.com',
        'claim_token' => $token = Str::random(32),
        'expires_at' => now()->addDays(7),
    ]);

    /* --- Request --- */
    $url = URL::signedRoute('invitation.update', ['token' => $token]);
    $response = $this->post($url, [
        'name' => 'John Doe',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    /* --- Assert HTTP response status --- */
    expect($response->status())->toBe(302);
    expect($response->getTargetUrl())->toBe(route('dashboard'));

    /* --- Assert DB State --- */
    $user = User::where('email', 'claim@example.com')->first();
    expect($user->name)->toBe('John Doe');
    expect($user->password)->not->toBeNull();
    expect($user->email_verified_at)->not->toBeNull();

    $this->assertDatabaseMissing('account_invitations', [
        'id' => $invitation->id,
    ]);

    $this->assertAuthenticated();
});

test('user cannot claim their account with invalid data', function () {
    /* --- Setup --- */
    $invitation = AccountInvitation::create([
        'email' => 'invalid@example.com',
        'claim_token' => $token = Str::random(32),
        'expires_at' => now()->addDays(7),
    ]);

    /* --- Request --- */
    $url = URL::signedRoute('invitation.update', ['token' => $token]);
    $response = $this->post($url, [
        'name' => '', // invalid name
        'password' => 'short', // invalid password length
        'password_confirmation' => 'short',
    ]);

    /* --- Assert HTTP response status --- */
    expect($response->status())->toBe(302);

    /* --- Assert HTTP response message/error --- */
    expect(session('errors')->get('name'))->not->toBeNull();
    expect(session('errors')->get('password'))->not->toBeNull();
    
    $this->assertGuest();
});

test('user cannot claim their account with an invalid token', function () {
    /* --- Request --- */
    $url = URL::signedRoute('invitation.update', ['token' => 'invalid-token']);
    $response = $this->post($url, [
        'name' => 'John Doe',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    /* --- Assert HTTP response status --- */
    expect($response->status())->toBe(404);
});
