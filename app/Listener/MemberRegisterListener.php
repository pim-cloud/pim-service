<?php

declare(strict_types=1);

namespace App\Listener;

use App\Event\SendMailEvent;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;

/**
 * @Listener
 */
class MemberRegisterListener implements ListenerInterface
{

    public function listen(): array
    {
        return [
            SendMailEvent::class,
        ];
    }

    public function process(object $event)
    {
        output('监听到邮件发送事件');
    }
}