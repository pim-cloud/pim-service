<?php
declare(strict_types=1);

namespace App\Controller;

use App\Redis\OnLine;
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
        $sfd = 'web:' . $fd;//web连接标识

        $uid = OnLine::getInstance()->getUidByFd($sfd);
        if (!empty($uid)) {
            //清除在线标识和uid映射
            OnLine::getInstance()->clearOnLineMember($sfd, $uid);
        } else {
            var_dump('未查询到信息 fd: ' . $sfd . '  掉线信息');
        }
    }

    public function onOpen($server, Request $request): void
    {
        if (is_null($request->server['query_string'])) {
            $server->close($request->fd);
        }
        $member = $this->auth->getPayload($request->server['query_string']);
        if (!isset($member['uid']) || empty($member['uid'])) {
            $server->close($request->fd);
        }
        //获取web登录token
        $webToken = redis()->hGet('u:token:' . $member['uid'], 'web');
        if ($webToken != $request->server['query_string']) {
            $server->close($request->fd);
        }

        //websocket是给web单独连接
        $uid = 'web:' . $member['uid'];
        $fd = 'web:' . $request->fd;

        OnLine::getInstance()->setOnline($uid, $fd);
        OnLine::getInstance()->setOnline($fd, $uid);
        $server->push($request->fd, 'Opened');
    }
}