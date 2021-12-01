<?php

declare(strict_types=1);
/**
 * mail config
 */
return [
    'default' => [
        'smtp_debug' => env('MAIL_SMTP_DEBUG', 0),
        'host' => env('MAIL_HOST'),
        'port' => env('MAIL_PORT'),
        'username' => env('MAIL_USERNAME'),
        'password' => env('MAIL_PASSWORD'),
        'from_address' => env('MAIL_FROM_ADDRESS'),
        'from_nickname' => env('MAIL_FROM_NICKNAME'),
        'char' => env('MAIL_CHAR', 'UTF-8'),
    ]
];
