<?php
declare(strict_types=1);

namespace App\Controller;

use App\Redis\OnLine;
use Swoole\Http\Request;
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

    public function onMessage($server, Frame $frame): void
    {
        if ($frame->data === 'ping') {
            $server->push($frame->fd, 'pong');
        }
    }

    public function onClose($server, int $fd, int $reactorId): void
    {
        $uid = OnLine::getInstance()->getUidByFd('web:', $fd);
        if (!empty($uid)) {
            //清除在线标识和uid映射
            OnLine::getInstance()->clearOnLineMember('web:' . $fd, 'web:' . $uid);
        } else {
            var_dump('未查询到信息 fd: ' . $fd . '  掉线信息');
        }
    }

    public function onOpen($server, Request $request): void
    {
        if (is_null($request->server['query_string'])) {
            $server->close($request->fd);
        }
        $member = $this->auth->getPayload($request->server['query_string']);
        if (isset($member['exp']) && $member['exp'] <= time()) {
            var_dump('token已经过期');
            $server->close($request->fd);
        }
        //获取web登录token
        $webToken = redis()->hGet('u:token:' . $member['uid'], 'web');
        if ($webToken != $request->server['query_string']) {
            $server->close($request->fd);
        }
        //websocket是给web单独连接
        OnLine::getInstance()->setOnline('web:', $member['uid'], $request->fd);
        OnLine::getInstance()->setOnline('web:', $request->fd, $member['uid']);
        $server->push($request->fd, 'Opened');
    }
}