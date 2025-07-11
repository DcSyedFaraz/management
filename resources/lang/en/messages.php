<?php
return [
    'nav' => [
        'dashboard' => 'Dashboard',
        'profile' => 'Profile',
        'logout' => 'Log Out',
    ],
    'profile' => [
        'title' => 'Profile',
        'update_password' => [
            'title' => 'Update Password',
            'description' => 'Ensure your account is using a long, random password to stay secure.',
            'current_password' => 'Current Password',
            'new_password' => 'New Password',
            'confirm_password' => 'Confirm Password',
            'save' => 'Save',
            'saved' => 'Saved.',
        ],
    ],
    'reset_password' => [
        'subject' => 'Your password reset code',
        'title' => 'Reset Password',
        'greeting' => 'Dear user,',
        'intro' => 'You have requested to reset your password. Please use the code below to complete the process:',
        'security' => 'For security reasons, this code is time-limited:',
        'valid_for' => '⏱️ Valid for 10 minutes',
        'footer' => 'If you did not make this request, you can ignore this email or contact us if you suspect your account has been compromised.',
        'success' => 'Password successfully reset.',
    ],
];
