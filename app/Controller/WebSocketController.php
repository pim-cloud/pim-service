<?php
declare(strict_types=1);

namespace App\Controller;

use App\Redis\OnLine;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Websocket\Frame;
use Hyperf\Di\Annotation\Inject;
use Qbhy\HyperfAuth\AuthManager;
use Hyperf\Contract\OnOpenInterface;
use Hyperf\Contract\OnCloseInterface;
use Hyperf\Contract\OnMessageInterface;
use function PHPUnit\Framework\isNull;

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
            output('用户掉线*清除fd映射信息fd:web:' . $fd . ' uid:web:' . $uid);
            OnLine::getInstance()->clearOnLineMember('web:' . $fd, 'web:' . $uid);
        } else {
            output('用户掉线*未查询到fd:web:' . $fd . ' 的信息*清除映射失败');
        }
    }

    public function onOpen($server, Request $request): void
    {
        if (!isset($request->server['query_string']) || empty($request->server['query_string'])) {
            $server->close($request->fd);
            return;
        }

        $token = $request->server['query_string'];
        $member = $this->auth->getPayload($token);
        if (isset($member['exp']) && $member['exp'] <= time()) {
            $server->close($request->fd);
            return;
        }
        //获取web登录token
        $webToken = redis()->hGet('u:token:' . $member['uid'], 'web');
        if ($webToken != $token) {
            $server->close($request->fd);
            return;
        }
        //websocket是给web单独连接
        OnLine::getInstance()->setOnline('web:', $member['uid'], $request->fd);
        OnLine::getInstance()->setOnline('web:', $request->fd, $member['uid']);
        output('uid:' . $member['uid'] . '*连接成功*fd:' . $request->fd);
        $server->push($request->fd, 'Opened');
    }
}