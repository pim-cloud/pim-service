<?php

declare(strict_types=1);

namespace App\Redis;

use App\Redis\Structure\Stream;

class MessageQueue extends Stream
{

    /**
     * 投递一条消息
     * @param string $queue
     * @param array $messages
     * @return string
     */
    public function push(string $queue, array $messages)
    {
        return $this->add($queue, $messages);
    }

    /**
     * 从消费组中弹出一条消息,非阻塞模式
     * @param string $queue
     * @param string $group
     * @param string $consumer
     * @return array
     */
    public function pop(string $queue, string $group, string $consumer)
    {
        return $this->getReadGroup($queue, $group, $consumer);
    }

    /**
     * 创建消费组
     * @param string $queue
     * @param string $group
     * @return mixed
     */
    public function createConsumerGroup(string $queue, string $group)
    {
        return $this->xGroup('CREATE', $queue, $group);
    }

    /**
     * 删除消费组
     * @param string $queue
     * @param string $group
     * @return mixed
     */
    public function delConsumerGroup(string $queue, string $group)
    {
        return $this->xGroup('DELGROUP', $queue, $group);
    }

    /**
     * 消息ack
     * @param $queue
     * @param $group
     * @param $ids
     * @return int
     */
    public function acks($queue, $group, $ids)
    {
        output('消息ack*'.$ids);
        return $this->ack($queue, $group, [$ids]);
    }
}