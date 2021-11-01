<?php

declare(strict_types=1);

namespace App\Redis\Structure;

class Set extends AbstractRedis
{

    protected $prefix = 'redis';

    protected $key = 'set';

    /**
     * 将元素加入集合
     * @param string $key
     * @param mixed ...$value
     * @return bool|int
     */
    public function add(string $key, ...$value)
    {
        return $this->redis()->sAdd($this->getKey($key), ...$value);
    }

    /**
     * 删除集合中的成员
     * @param string $key
     * @return int
     */
    public function del(string $key, $value)
    {
        return $this->redis()->sRem($this->getKey($key), $value);
    }

    /**
     * 判断是否在集合中
     * @param string $key
     * @param $value
     * @return bool
     */
    public function sisMember(string $key, $value)
    {
        return $this->redis()->sismember($this->getKey($key), $value);
    }

    /**
     * 返回集合中所有成员
     * @param string $key
     * @return array
     */
    public function sMembers(string $key)
    {
        return $this->redis()->sMembers($this->getKey($key));
    }
}