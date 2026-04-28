<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('confirm password screen can be rendered', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('password.confirm'));

    $response->assertStatus(200);

    // This assert will not work in our docker images, as we only copy the compiled assets.
    // TODO: Replace with E2E test
    // $response->assertInertia(fn (Assert $page) => $page
    //     ->component('auth/confirm-password')
    // );
});

test('password confirmation requires authentication', function () {
    $response = $this->get(route('password.confirm'));

    $response->assertRedirect(route('login'));
});