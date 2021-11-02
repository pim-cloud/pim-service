<?php

declare(strict_types=1);

namespace App\Redis\Structure;


class Zset extends AbstractRedis
{
    /**
     * @param string $key
     * @param $value
     * @param $score
     * @return int
     */
    public function add(string $key, $value, $score)
    {
        return $this->redis()->zAdd($key, $score, $value);
    }
}