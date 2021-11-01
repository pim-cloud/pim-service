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
        return $this->add($messages, $queue);
    }

    /**
     * 从消费组中弹出一条消息,非阻塞模式
     * @param string $group
     * @param string $consumer
     * @param string $queue
     * @return array
     */
    public function pop(string $group, string $consumer, string $queue)
    {
        return $this->getReadGroup($group, $consumer, $queue);
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
}