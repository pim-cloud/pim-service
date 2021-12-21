<?php

declare(strict_types=1);

namespace App\Redis\Structure;

use Hyperf\Redis\Redis;
use Hyperf\Utils\ApplicationContext;

abstract class AbstractRedis
{
    public function redis()
    {
        return ApplicationContext::getContainer()->get(Redis::class);
    }

    public static function getInstance()
    {
        return ApplicationContext::getContainer()->get(static::class);
    }
}