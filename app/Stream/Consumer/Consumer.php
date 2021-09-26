<?php

namespace App\Stream\Consumer;

use Psr\Container\ContainerInterface;

class Consumer
{
    public const CREATE = 'CREATE';
    public const DELGROUP = 'DELGROUP';

    public const STREAM = 'STREAM';
    public const GROUPS = 'GROUPS';
    public const CONSUMERS = 'CONSUMERS';

    public const MESSAGEID = '0';
    public const NEWENTRY = '>';

    protected $redis;

    public function __construct(ContainerInterface $container)
    {
        $this->redis = $container->get(\Hyperf\Redis\Redis::class);
    }

    /**
     * 创建队列消费组
     * @param string $queuename 队列名
     * @param string $groupname 消费组名
     * @param string $messageId
     * @return mixed
     */
    public function createConsumerGroup(string $queuename, string $groupname, string $messageId = self::MESSAGEID)
    {
        return $this->redis->xGroup(self::CREATE, $queuename, $groupname, $messageId, true);
    }

    /**
     * 从消费组中获取消息,被传递给消费者且未ack的消息，将会被储存在消费组内的待处理条目列表上，即已送达但尚未确认的消息ID列表。
     * @param string $groupname 消费组
     * @param string $consumer 消费者名
     * @param string $queuename 队列名
     * @param string $entry 条目 '>'表示特殊ID，只接受没有被消费过的消息
     * @param string $count 条数
     * @param string $block 阻塞时间
     * @return array
     */
    public function getConsumerGroupMsg(string $groupname, string $consumer, string $queuename, string $entry = self::NEWENTRY, $count = 1, $block = 5000)
    {
        return $this->redis->xReadGroup($groupname, $consumer, [$queuename => $entry], $count, $block);
    }

    /**
     * 获取队列中待处理的消息
     * @param string $queuename 队列名
     * @param string $groupname 组名
     * @param string $consumer 消费者
     * @param string $start 开始ID
     * @param string $end 结束ID
     * @param int $count 条数
     * @return array
     */
    public function getPendingMsg(string $queuename, string $groupname, string $consumer = '', $start = '-', $end = '+', $count = 1): array
    {
        $pending = $this->redis->xPending($queuename, $groupname, $start, $end, $count, $consumer);
        if (!empty($pending)) {
            return [
                'messageid' => $pending[0][0],
                'consumername' => $pending[0][1],
                'millisecondsago' => $pending[0][2],
                'readsecond' => $pending[0][3]
            ];
        }
        return [];
    }


    /**
     * 根据ID范围获取数据
     * @param string $queuename 队列名
     * @param string $start 开始ID '-'表示最小ID
     * @param string $end 结束ID '+'表示最大ID
     * @param int $count 条数
     * @return array
     */
    public function getRangeMsg(string $queuename, string $start = '-', string $end = '+', $count = 1): array
    {
        $msg = $this->redis->xRange($queuename, $start, $end, $count);
        if (!empty($msg)) {
            return $msg;
        }
        return [];
    }

    /**
     * @param array $queuename 队列信息 [$queuename=>'特殊ID']
     * '$' 表示使用队列已经储存的最大ID作为最后一个ID，仅监听从这个ID开始后接收到的数据，相当于unix tail -f
     * '0' 表示获取ID > 0-0 的消息，就是获取所有消息的意思
     * [$queuename1=>'特殊ID',$queuename2=>'特殊ID'] 通过这种方式获取多个队列的消息
     * @param int $count 条数
     * @param mixed $block 超时时间 0 永不超时，如果设置超时时间，则变成了一个阻塞命令。
     * @return array
     */
    public function getReadMsg(array $queuename, int $count = 1, $block = 0): array
    {
        return $this->redis->xRead($queuename, $count, $block);
    }

    /**
     * 消息确认
     * @param string $queuename 队列名
     * @param string $groupname 消费组名
     * @param array $messageIds 消息IDs ['1530063064286-0', '1530063064286-1']
     * @return int 确认的消息条数
     */
    public function ack(string $queuename, string $groupname, array $messageIds): int
    {
        return $this->redis->xAck($queuename, $groupname, $messageIds);
    }

    /**
     * 删除消费组
     * @param string $queuename 队列名
     * @param string $groupname 组名
     * @return mixed
     */
    public function delConsumerGroup(string $queuename, string $groupname)
    {
        return $this->redis->xGroup(self::DELGROUP, $queuename, $groupname);
    }

    /**
     * 获取消费者信息
     * @param string $queuename 队列名
     * @param string $groupname 组名
     * @return mixed
     */
    public function getConsumerInfo(string $queuename, string $groupname)
    {
        return $this->redis->xInfo(self::CONSUMERS, $queuename, $groupname);
    }

    /**
     * 获取消费组信息
     * @param string $queuename 队列名
     * @return mixed
     */
    public function getConsumerGroupInfo(string $queuename)
    {
        return $this->redis->xInfo(self::GROUPS, $queuename);
    }

    /**
     * 获取队列信息
     * @param string $queuename 队列名
     * @return mixed
     */
    public function getQueueInfo(string $queuename)
    {
        return $this->redis->xInfo(self::STREAM, $queuename);
    }

    /**
     * 删除队列中的消息
     * @param string $queuename 队列名
     * @param array $ids ['1530115304877-0', '1530115305731-0']
     * @return int
     */
    public function delMsg(string $queuename, array $ids): int
    {
        return $this->redis->xDel($queuename, $ids);
    }
}