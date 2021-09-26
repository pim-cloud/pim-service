<?php

namespace App\Stream\Producer;


use Psr\Container\ContainerInterface;

class Producer
{
    protected $redis;

    public function __construct(ContainerInterface $container)
    {
        $this->redis = $container->get(\Hyperf\Redis\Redis::class);
    }

    /**
     * 投递消息
     * @param string $queuename 队列名称
     * @param array $message 消息内容
     * @param string $id 消息ID 默认 * 自动生成
     * @param int $maxlength 队列最大长度默认0，不限制
     * @return string 唯一消息ID(根据毫秒时间戳生成+同时投递消息的条数递增)
     */
    public function push(string $queuename, array $message, $id = '*', $maxlength = 0): string
    {
        return $this->redis->xadd($queuename, $id, $message, $maxlength);
    }


    /**
     * 设置队列长度
     * @param string $queuename
     * @param int $maxlength
     * @param bool $isApproximate
     * @return int
     */
    public function setQueueLength(string $queuename, int $maxlength, bool $isApproximate = true): int
    {
        return $this->redis->xTrim($queuename, $maxlength, $isApproximate);
    }

    /**
     * 删除队列中的message
     * @param string $queuename
     * @param array $messageIds
     * @return int
     */
    public function del(string $queuename, array $messageIds)
    {
        return $this->redis->xDel($queuename, $messageIds);
    }

    /**
     * 获取队列长度
     * @param string $queuename 队列名
     * @return int
     */
    public function getQueueLength(string $queuename): int
    {
        return $this->redis->xLen($queuename);
    }
}