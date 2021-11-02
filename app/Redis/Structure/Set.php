<?php

declare(strict_types=1);

namespace App\Redis\Structure;

class Set extends AbstractRedis
{

    /**
     * 将元素加入集合
     * @param string $key
     * @param mixed ...$value
     * @return bool|int
     */
    public function add(string $key, ...$value)
    {
        return $this->redis()->sAdd($key, ...$value);
    }

    /**
     * 删除集合中的成员
     * @param string $key
     * @return int
     */
    public function del(string $key, $value)
    {
        return $this->redis()->sRem($key, $value);
    }

    /**
     * 判断是否在集合中
     * @param string $key
     * @param $value
     * @return bool
     */
    public function sisMember(string $key, $value)
    {
        return $this->redis()->sismember($key, $value);
    }

    /**
     * 返回集合中所有成员
     * @param string $key
     * @return array
     */
    public function sMembers(string $key)
    {
        return $this->redis()->sMembers($key);
    }
}