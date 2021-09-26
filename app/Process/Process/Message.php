<?php

namespace App\Process\Process;

use Hyperf\Process\AbstractProcess;
use Hyperf\Process\ProcessManager;

class Message extends AbstractProcess
{
    public $name = 'message';

    public function handle(): void
    {
        $consumer = $this->container->get(\App\Stream\Consumer\Consumer::class);

        //创建消费组
        $queuename = getLocalUnique();

        $consumer->createConsumerGroup($queuename, $queuename);

        while (ProcessManager::isRunning()) {
            $data = $consumer->getConsumerGroupMsg($queuename, $queuename, $queuename);
            if (empty($data)) {
                continue;
            }
            $msgId = key($data[$queuename]);
            $msg = $data[$queuename][$msgId];
            $consumerLogic = $this->container->get(\App\Process\Consumer\EventExplain::class);
            $msg['msg_id'] = $msgId;
            $consumerLogic->consume($msg);
        }
    }
}