<?php

namespace App\Controller;

use App\Stream\Consumer\Consumer;
use App\Stream\Producer\Producer;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;

/**
 * @Controller(prefix="index")
 */
class IndexController extends AbstractController
{
    /**
     * @GetMapping(path="index")
     */
    public function index(Producer $producer, Consumer $consumer)
    {
        return $id = enter(['name'=>rand()]);


        $queue = 'test_queue';
        $group = 'group1';
        $consumers = 'consumer1';

        //var_dump($consumer->getPendingMsg($queue,'group1','consumer1'));
        while (1) {
            sleep(1);
            $producer->push($queue,['name'=>rand()]);

            $rangeMsg = $consumer->getReadMsg([$queue=>'0']);
            $consumer->delMsg($queue,[array_keys($rangeMsg[$queue])[0]]);
            var_dump($rangeMsg);

            //创建消费组
            /*$consumer->createConsumerGroup($queue,$group);

            //从消费组获取消息
            $msg = $consumer->getConsumerGroupMsg($group, $consumers, $queue);
            var_dump($msg);
            echo '获取等待列表';*/
            //获取未ack消息列表
            /*$pend = $consumer->getPendingMsg($queue, $group, $consumers);
            var_dump($pend);
            if (!empty($pend)) {
                $msgId = $pend['messageid'];
                var_dump($pend) . PHP_EOL;
                //ack消息
                //echo 'ack 消息:' . $msgId;
                var_dump($consumer->ack($queue, $group, [$msgId])) . PHP_EOL;
            }*/
        }
    }

}