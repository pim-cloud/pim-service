<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\Contract\OnReceiveInterface;

class TcpController implements OnReceiveInterface
{
    public function onReceive($server, int $fd, int $reactorId, string $data): void
    {
        var_dump('接收数据事件');
        var_dump($fd);
        var_dump($reactorId);
        var_dump($data);
        //$server->send($fd, 'recv:' . $data);
    }

    public function onConnect($server, $fd)
    {
        var_dump('打开tcp');
    }

    public function onClose($server, $fd)
    {
        var_dump('关闭连接事件');
    }
}