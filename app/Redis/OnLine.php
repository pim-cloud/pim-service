<?php

declare(strict_types=1);

namespace App\Redis;

use App\Redis\Structure\Hash;

class OnLine extends Hash
{
    protected $key = 'online';

    /**
     * 设置在线设备和连接标识
     * @param string $key
     * @param string $value
     * @return bool
     */
    public function setOnline(string $key, string $value)
    {
        return $this->set($this->key, $key, $value);
    }

    /**
     * 根据fd获取uid
     * @param string $fd
     * @return string|false
     */
    public function getUidByFd(string $fd)
    {
        return $this->get($this->key, $fd);
    }

    /**
     * 清除在线设备连接表示和uid
     * @param mixed ...$member
     * @return bool|int
     */
    public function clearOnLineMember(...$member)
    {
        return $this->del($this->key, ...$member);
    }
}