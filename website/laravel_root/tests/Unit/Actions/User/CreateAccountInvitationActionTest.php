<?php

namespace Tests\Unit\Actions\User;

use App\Actions\User\CreateAccountInvitationAction;
use App\Exceptions\UserAlreadyExistsException;
use App\Mail\ClaimAccountMail;
use App\Models\AccountInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->action = app(CreateAccountInvitationAction::class);
});

test('can create a new account invitation', function () {
    Mail::fake();
    $email = 'new-user@example.com';

    ($this->action)($email);

    $this->assertDatabaseHas('account_invitations', [
        'email' => $email,
    ]);

    Mail::assertQueued(ClaimAccountMail::class, function ($mail) use ($email) {
        return $mail->hasTo($email);
    });
});

test('can refresh an existing account invitation', function () {
    Mail::fake();
    $email = 'existing-invite@example.com';
    
    $oldInvitation = AccountInvitation::create([
        'email' => $email,
        'claim_token' => 'old-token',
        'expires_at' => now()->subDays(1), // Expired
    ]);

    ($this->action)($email);

    $newInvitation = $oldInvitation->fresh();
    expect($newInvitation->claim_token)->not->toBe('old-token');
    expect($newInvitation->expires_at->isFuture())->toBeTrue();

    Mail::assertQueued(ClaimAccountMail::class, function ($mail) use ($email) {
        return $mail->hasTo($email);
    });
});

test('cannot invite a user who already exists', function () {
    $user = User::factory()->create();

    $this->expectException(UserAlreadyExistsException::class);

    ($this->action)($user->email);
});
