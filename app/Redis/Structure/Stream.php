<?php

declare(strict_types=1);

namespace App\Redis\Structure;

/**
 * Class Stream
 * @package App\Redis\Structure
 * @time 2021/11/1
 * @contact  jksusuppx@qq.com
 */
class Stream extends AbstractRedis
{
    protected $prefix = 'redis:stream';

    protected $key = 'stream';

    /**
     * 投递消息
     * @param array $messages 消息内容
     * @param string $queue 队列名称
     * @param string $id 消息ID 默认 * 自动生成
     * @param int $maxlength 队列最大长度默认0，不限制
     * @param bool $isApproximate
     * @return string 唯一消息ID(根据毫秒时间戳生成+同时投递消息的条数递增)
     * @example
     * <pre>
     * $this->add('mystream', "*", ['field' => 'value']);
     * $this->add('mystream', "*", ['field' => 'value'], 10);
     * $this->add('mystream', "*", ['field' => 'value'], 10, true);
     * </pre>
     */
    public function add(array $messages, string $queue = '', string $id = '*', int $maxlength = 0, $isApproximate = false)
    {
        return $this->redis()->xadd($this->getKey() . $queue, $id, $messages, $maxlength, $isApproximate);
    }

    /**
     * 删除队列中的message
     * @param string $queue
     * @param array $ids
     * @return int
     * @example
     * <pre>
     * $this->del('mystream', ['1530115304877-0', '1530115305731-0']);
     * </pre>
     */
    public function del(string $queue, array $ids)
    {
        return $this->redis()->xDel($queue, $ids);
    }

    /**
     * 消息ack
     * @param string $queue 队列名
     * @param string $group 消费组名
     * @param array $ids 消息IDs ['1530063064286-0', '1530063064286-1']
     * @return int 确认的消息条数
     */
    public function ack(string $queue, string $group, array $ids)
    {
        return $this->redis()->xAck($queue, $group, $ids);
    }


    /**
     * 消费组命令
     * @param string $operation
     * @param string $key
     * @param string $group
     * @param string $msgId
     * @param false $mkStream
     * @return mixed
     */
    public function xGroup(string $operation, string $key, string $group, $msgId = '0', $mkStream = false)
    {
        return $this->redis()->xGroup($operation, $key, $group, $msgId, $mkStream);
    }


    /**
     * 更具选项获取信息
     * @param string $operation e.g.: 'CONSUMERS', 'GROUPS', 'STREAM', 'HELP'
     * @param string $queue
     * @param string $group
     * @return mixed
     */
    public function getInfo(string $queue, string $group, string $operation = 'GROUPS')
    {
        return $this->redis()->xInfo($operation, $queue, $group);
    }


    /**
     * 设置队列长度
     * @param string $queue
     * @param int $maxlength
     * @param bool $isApproximate
     * @return int
     */
    public function setLength(string $queue, int $maxlength, bool $isApproximate): int
    {
        return $this->redis()->xTrim($queue, $maxlength, $isApproximate);
    }

    /**
     * 获取队列长度
     * @param string $queue 队列名
     * @return int
     */
    public function getLength(string $queue)
    {
        return $this->redis()->xLen($queue);
    }

    /**
     * 获取队列中待处理的消息
     * @param string $queue 队列名
     * @param string $group 组名
     * @param string $consumer 消费者
     * @param string $start 开始ID
     * @param string $end 结束ID
     * @param int $count 条数
     * @return array
     * @example
     * <pre>
     * $this->getPending('mystream', 'mygroup');
     * $this->getPending('mystream', 'mygroup', '-', '+', 1, 'consumer-1');
     * </pre>
     */
    public function getPending(string $queue, string $group, string $consumer = '', $start = '-', $end = '+', $count = 1): array
    {
        $pending = $this->redis()->xPending($queue, $group, $start, $end, $count, $consumer);
        if (!empty($pending) && $count == 1) {
            return [
                'id' => $pending[0][0],
                'consumer' => $pending[0][1],
                'millisecondsago' => $pending[0][2],
                'readsecond' => $pending[0][3]
            ];
        }
        return [];
    }

    /**
     * 根据ID范围获取数据,会自动过滤已经删除的消息
     * @param string $queue 队列名
     * @param string $start 开始ID '-'表示最小ID
     * @param string $end 结束ID '+'表示最大ID
     * @param int $count 条数
     * @return array
     */
    public function getRange(string $queue, string $start = '-', string $end = '+', $count = 1): array
    {
        return $this->redis()->xRange($queue, $start, $end, $count);
    }

    /**
     * 以阻塞或非阻塞方式获取消息列表
     * @param array $queue 队列信息 [$queue=>'特殊ID']
     * '$' 表示使用队列已经储存的最大ID作为最后一个ID，仅监听从这个ID开始后接收到的数据，相当于unix tail -f
     * '0' 表示获取ID > 0-0 的消息，就是获取所有消息的意思
     * [$queue1=>'特殊ID',$queue2=>'特殊ID'] 通过这种方式获取多个队列的消息
     * @param int $count 条数
     * @param mixed $block 超时时间 0 永不超时，如果设置超时时间，则变成了一个阻塞命令。
     * @return array
     */
    public function getRead(array $queue, int $count = 1, $block = 0): array
    {
        return $this->redis()->xRead($queue, $count, $block);
    }

    /**
     * 从消费组中获取消息,被传递给消费组且未ack的消息，
     * 将会被储存在消费组内的待处理条目列表上，即已送达但尚未确认的消息ID列表。
     * @param string $group 消费组
     * @param string $consumer 消费者名
     * @param string $queue 队列名
     * @param string $entry 条目 '>'表示特殊ID，只接受没有被消费过的消息
     * @param string $count 条数
     * @param string $block 阻塞时间
     * @return array
     * @example
     * <pre>
     * // Consume messages for 'mygroup', 'consumer1'
     * $this->getReadGroup('mygroup', 'consumer1', ['s1' => 0, 's2' => 0]);
     * // Read a single message as 'consumer2' for up to a second until a message arrives.
     * $this->getReadGroup('mygroup', 'consumer2', ['s1' => 0, 's2' => 0], 1, 1000);
     * </pre>
     */
    public function getReadGroup(string $group, string $consumer, string $queue, string $entry = '>', $count = 1, $block = 5000)
    {
        return $this->redis()->xReadGroup($group, $consumer, [$queue => $entry], $count, $block);
    }
}