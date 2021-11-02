<?php

declare(strict_types=1);

namespace App\Redis\Structure;


class Strings extends AbstractRedis
{
    /**
     * 缓存
     * @param string $key
     * @param string $val
     * @param null $expires
     * @return bool
     */
    public function set(string $key, string $val, $expires = null)
    {
        return $this->redis()->set($key, $val, $expires);
    }


    /**
     * 获取缓存
     * @param string $key
     * @return false|mixed|string
     */
    public function get(string $key)
    {
        return $this->redis()->get($key);
    }

    /**
     * 删除缓存
     * @param string $key
     * @return int
     */
    public function del(string $key)
    {
        return $this->redis()->del($key);
    }
}