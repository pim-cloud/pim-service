<?php

declare(strict_types=1);

namespace App\Redis;

use App\Redis\Structure\Hash;

class OnLine extends Hash
{
    protected $key = 'redis:online:mapping';

    /**
     * 设置在线设备和连接标识
     * @param string $channel
     * @param  $hashKey
     * @param  $value
     * @return bool
     */
    public function setOnline(string $channel, $hashKey, $value)
    {
        return $this->set($this->key, $channel . $hashKey, $channel . $value);
    }

    /**
     * 根据fd获取uid
     * @param string $channel
     * @param  $fd
     * @return string
     */
    public function getUidByFd(string $channel, $fd)
    {
        $uid = $this->get($this->key, $channel . $fd);
        return (int)substr($uid,4);
    }

    /**
     * 获取fd
     * @param string $channel
     * @param string $uid
     * @return int
     */
    public function getFdByUid(string $channel, string $uid)
    {
        $fd = $this->get($this->key, $channel . $uid);
        return (int)substr($fd,4);
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