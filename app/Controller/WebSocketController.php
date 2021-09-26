<?php
declare(strict_types=1);

namespace App\Controller;

use App\Tools\RedisTools;
use Swoole\Http\Request;
use Swoole\Websocket\Frame;
use Hyperf\Contract\OnOpenInterface;
use Hyperf\Contract\OnCloseInterface;
use Hyperf\Contract\OnMessageInterface;
use Hyperf\Di\Annotation\Inject;

class WebSocketController implements OnMessageInterface, OnOpenInterface, OnCloseInterface
{
    /**
     * @Inject
     * @var RedisTools
     */
    protected $redisTools;

    public function onMessage($server, Frame $frame): void
    {
        if ($frame->data === 'ping') {
            $server->push($frame->fd, 'pong');
        }
    }

    public function onClose($server, int $fd, int $reactorId): void
    {
        //根据fd获取当前下线的uid
        $uid = $this->redisTools->getUidByMappingFd((int)$fd);
        if (!empty($uid)) {
            //清除fd
            $this->redisTools->delH5OnLineFd($fd);
            //清除uid在线设备hash 掉线fd
            $this->redisTools->redis->hDel('uidOnLine>>' . $uid, 'h5_fd');
        }
        var_dump('closed');
    }

    /**
     * 1.获取当前连接用户 uid
     * 2.用户 uid 映射 web fd
     * 3.fd 映射 uid
     */
    public function onOpen($server, Request $request): void
    {
        if (is_null($request->get['authentication'])) {
            $server->close($request->fd);
        }

        $uid = $this->redisTools->getUidByToken((string)$request->get['authentication']);

        if (empty($uid) || !$uid) {
            $server->close($request->fd);
        }

        //增加在线客户端hash
        $this->redisTools->setH5OnLineFd((string)$uid, (int)$request->fd);
        //增加uid fd 互相映射
        $this->redisTools->setFdMappingUid((int)$request->fd, (string)$uid);

        $server->push($request->fd, 'Opened');
    }
}