<?php

return [
    'tenant_invitation' => [
        'subject' => 'You have been invited to join :tenant',
        'greeting' => 'Hello!',
        'intro' => 'You have been invited to join the organization **:tenant**.',
        'roles_intro' => 'You will be assigned the following roles:',
        'accept_action' => 'Accept Invitation',
        'decline_action' => 'Decline Invitation',
        'outro' => 'If you were not expecting this invitation, you can safely ignore this email.',
        'salutation' => 'Regards, :name',
    ],
    'claim_account' => [
        'subject' => 'Welcome to :name - Claim your account',
        'greeting' => 'Welcome!',
        'intro' => 'An account has been reserved for you on **:name**. Please click the button below to set your password and complete your registration.',
        'action' => 'Claim Account',
        'outro' => 'This link is private and should not be shared.',
        'salutation' => 'Regards, :name',
    ],
];
