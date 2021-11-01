<?php

declare(strict_types=1);

namespace App\Redis\Structure;


class Hash extends AbstractRedis
{
    protected $prefix = 'redis';

    protected $key = 'hash';

    /**
     * 给哈希表key设置一组字段值
     * @param string $table hash表名
     * @param string $field 字段
     * @param string $value 值
     * @return bool
     */
    public function set(string $table, string $field, string $value)
    {
        return $this->redis()->hSet($this->getKey($table), $field, $value);
    }

    /**
     * 从哈希表key中获取给定的一个字段值
     * @param string $key
     * @param $hashKey
     * @return false|string
     */
    public function get(string $key, $hashKey)
    {
        return $this->redis()->hGet($this->getKey($key), $hashKey);
    }

    /**
     * 从哈希表key中获取全部的字段和值
     * @param string $key
     * @return array
     */
    public function getAll(string $key)
    {
        return $this->redis()->hGetAll($this->getKey($key));
    }

    /**
     * 判断哈希表key中是否存一个或多个字段
     * @param string $key
     * @param mixed ...$field
     * @return bool|int
     */
    public function del(string $key, ...$field)
    {
        return $this->redis()->hDel($this->getKey($key), ...$field);
    }

    /**
     * 判断哈希表key中是否存在给定的字段
     * @param string $key
     * @param $value
     * @return bool
     */
    public function ifExists(string $key, $value): bool
    {
        return $this->redis()->hExists($this->getKey($key), $value);
    }

    /**
     * 获取哈希表中总字段数
     * @param string $key
     * @return false|int
     */
    public function count(string $key)
    {
        return $this->redis()->hLen($this->getKey($key));
    }
}