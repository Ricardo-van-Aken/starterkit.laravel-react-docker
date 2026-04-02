<?php

it('redirects to the login page when unauthenticated', function () {
    /* --- Request --- */
    $response = $this->get('/');

    /* --- Assert HTTP response status --- */
    $response->assertStatus(302);
    $response->assertRedirect(route('login'));
});
