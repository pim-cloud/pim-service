<?php

declare(strict_types=1);
/**
 * mail config
 */
return [
    'default' => [
        'mail_smtp_debug' => env('MAIL_SMTP_DEBUG', 0),
        'mail_host' => env('MAIL_HOST'),
        'mail_port' => env('MAIL_PORT'),
        'mail_username' => env('MAIL_USERNAME'),
        'mail_authorization_code' => env('MAIL_AUTHORIZATION_CODE'),
        'mail_from_address' => env('MAIL_FROM_ADDRESS'),
        'mail_from_nickname' => env('MAIL_FROM_NICKNAME'),
        'mail_char' => env('MAIL_CHAR', 'UTF-8'),
    ]
];
