<?php

namespace App\Process\Process;

use Hyperf\Process\AbstractProcess;
use Hyperf\Process\ProcessManager;

class Pending
{
    public $name = 'pending';

    public function handle(): void
    {
        //消费
        $consumer = $this->container->get(\App\Stream\Consumer\Consumer::class);

        //事件解析器
        $consumerLogic = $this->container->get(\App\Process\Consumer\EventExplain::class);

        $queuename = getLocalUnique();

        while (ProcessManager::isRunning()) {
            sleep(5);
            $pendMsg = $consumer->getPendingMsg($queuename, $queuename, $queuename, '-', '+', 1);

            var_dump($pendMsg);
            if (empty($pendMsg)) {
                continue;
            }

            $msgId = $pendMsg['messageid'];
            $time = $pendMsg['millisecondsago'];
            $num = $pendMsg['readsecond'];

            if ($time <= 10000) {
                continue;
            }

            var_dump('ID='.$msgId.' 的消息，在'.$time.'毫秒前被投放，消费了'.$num.'次；没有被ack!');

            $groupMsg = $consumer->getConsumerGroupMsg($queuename,$queuename,$queuename,'0');
            var_dump($groupMsg);
            /*foreach ($pendMsg as $key => $item) {
                $msgId = $item[0];
                $time = $item[2];
                $num = $item[3];
                var_dump('ID='.$msgId.' 的消息，在'.$time.'毫秒前被投放，消费了'.$num.'次；没有被ack!');
                $groupMsg = $consumer->getConsumerGroupMsg($queuename,$queuename,$queuename,$msgId);
                if (isset($groupMsg[$queuename]) && !empty($groupMsg[$queuename])) {
                    $msgId = key($groupMsg[$queuename]);
                    $msg = $groupMsg[$queuename][$msgId];
                    $msg['msg_id'] = $msgId;
                    $consumerLogic->consume($msg);
                }
            }*/
        }
    }
}