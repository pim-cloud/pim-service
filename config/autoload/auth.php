<?php
declare(strict_types=1);

return [
    'default' => [
        'guard' => 'sso',
        'provider' => 'users',
    ],
    'guards' => [
        'sso' => [
            'clients' => explode(',', env('AUTH_SSO_CLIENTS', 'web')),
            'redis' => function () {
                return make(\Hyperf\Redis\Redis::class);
            },
            'driver' => Qbhy\HyperfAuth\Guard\SsoGuard::class,
            'provider' => 'member',
            'secret' => env('SSO_JWT_SECRET'),
            'ttl' => (int) env('SIMPLE_JWT_TTL', 60 * 60 * 24),
            'refresh_ttl' => (int) env('SIMPLE_JWT_REFRESH_TTL', 60 * 60 * 24 * 2),
            'cache' => function () {
                return make(\Qbhy\HyperfAuth\HyperfRedisCache::class);
            },
        ],
    ],
    'providers' => [
        'member' => [
            'driver' => \Qbhy\HyperfAuth\Provider\EloquentProvider::class,
            'model' => \App\Model\Member::class,
        ],
    ],
];
