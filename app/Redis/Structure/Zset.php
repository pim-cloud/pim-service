<?php

declare(strict_types=1);

namespace App\Redis\Structure;


class Zset extends AbstractRedis
{
    protected $prefix = 'redis';

    protected $key = 'zset';

    /**
     * @param string $key
     * @param $value
     * @param $score
     * @return int
     */
    public function add(string $key, $value, $score)
    {
        return $this->redis()->zAdd($this->getKey($key), $score, $value);
    }
}