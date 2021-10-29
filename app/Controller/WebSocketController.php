<?php
declare(strict_types=1);

namespace App\Controller;

use Swoole\Http\Request;
use App\Tools\RedisTools;
use Swoole\Websocket\Frame;
use Hyperf\Di\Annotation\Inject;
use Qbhy\HyperfAuth\AuthManager;
use Hyperf\Contract\OnOpenInterface;
use Hyperf\Contract\OnCloseInterface;
use Hyperf\Contract\OnMessageInterface;

class WebSocketController implements OnMessageInterface, OnOpenInterface, OnCloseInterface
{

    /**
     * @Inject
     * @var AuthManager
     */
    protected $auth;

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

    public function onOpen($server, Request $request): void
    {
        if (is_null($request->get['Authentication'])) {
            $server->close($request->fd);
        }
        $authentication = mb_substr($request->get['Authentication'], 7);
        $member = $this->auth->getPayload($authentication);
        if (!isset($member['uid']) || empty($member['uid'])) {
            $server->close($request->fd);
        }
        $uid = $member['uid'];
        //获取web登录token
        $webToken = $this->redisTools->redis->hGet('u:token:' . $uid, 'web');
        if ($webToken <> $authentication) {
            $server->close($request->fd);
        }
        $this->redisTools->setH5OnLineFd((string)$uid, (int)$request->fd);//增加在线客户端hash
        $this->redisTools->setFdMappingUid((int)$request->fd, (string)$uid);//增加uid fd 互相映射
        $server->push($request->fd, 'Opened');
    }
}