<?php

namespace App\Process\Process;

use App\Redis\MessageQueue;
use Hyperf\Process\ProcessManager;
use Hyperf\Process\AbstractProcess;

class Message extends AbstractProcess
{
    public $name = 'message';

    public function handle(): void
    {
        $queue = getLocalUnique();
        MessageQueue::getInstance()->createConsumerGroup($queue, $queue);

        while (ProcessManager::isRunning()) {
            $data = MessageQueue::getInstance()->pop($queue, $queue, $queue);//弹出一条消息
            var_dump($data);
            if (empty($data)) {
                continue;
            }
            var_dump($data);
            $msgId = key($data[$queue]);
            $msg = $data[$queue][$msgId];
            $consumerLogic = $this->container->get(\App\Process\Consumer\EventExplain::class);
            $msg['msg_id'] = $msgId;
            $consumerLogic->consume($msg);
        }
    }
}