<?php

declare(strict_types=1);

namespace App\Redis\Structure;

use Hyperf\Redis\Redis;
use Hyperf\Utils\ApplicationContext;

abstract class AbstractRedis
{
    protected $prefix = 'redis';

    protected $key = '';

    public function redis()
    {
        return ApplicationContext::getContainer()->get(Redis::class);
    }

    public static function getInstance()
    {
        return ApplicationContext::getContainer()->get(static::class);
    }

    /**
     * 获取缓存key
     * @param string $key
     * @return string
     */
    public function getKey($key = ''): string
    {
        return $this->prefix . ':' . $this->key .':'. $key;
    }
}