<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Email Language Lines
    |--------------------------------------------------------------------------
    |
    | These language lines are used in transactional email templates.
    |
    */

    // Common
    'hello' => 'Hello!',
    'trouble_clicking' => "If you're having trouble clicking the button, copy and paste the URL below into your web browser:",

    // Email verification
    'verify_email' => [
        'subject' => 'Verify Email Address',
        'title' => 'Verify Email Address',
        'intro' => 'Please click the button below to verify your email address.',
        'button' => 'Verify Email Address',
        'outro' => 'If you did not create an account, no further action is required.',
    ],

    // Password reset
    'reset_password' => [
        'subject' => 'Reset Password Notification',
        'title' => 'Reset Password',
        'intro' => 'You are receiving this email because we received a password reset request for your account.',
        'button' => 'Reset Password',
        'expires' => 'This password reset link will expire in :count minutes.',
        'outro' => 'If you did not request a password reset, no further action is required.',
    ],

];
