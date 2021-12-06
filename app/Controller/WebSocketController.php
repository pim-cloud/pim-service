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
            output('用户掉线*清除fd映射信息fd:web:' . $fd . ' uid:web:' . $uid);
            OnLine::getInstance()->clearOnLineMember('web:' . $fd, 'web:' . $uid);
        } else {
            output('getContactGroups*未查询到fd:web:' . $fd . ' 的信息*清除映射失败');
        }
    }

    public function onOpen($server, Request $request): void
    {
        $member = $this->auth->getPayload($request->get['token']);
        OnLine::getInstance()->setWebOnLine($member['uid'], $request->fd);
        output('uid:' . $member['uid'] . '*连接成功*fd:' . $request->fd);
    }
}