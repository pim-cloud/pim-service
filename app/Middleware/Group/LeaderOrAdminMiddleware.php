<?php

declare(strict_types=1);

namespace App\Middleware\Group;

use App\Model\GroupMember;
use Hyperf\Di\Annotation\Inject;
use App\Exception\ValidateException;
use App\Exception\BusinessException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LeaderOrAdminMiddleware implements MiddlewareInterface
{

    /**
     * @Inject()
     * @var ContainerInterface
     */
    protected $container;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $token = $request->getHeaderLine('authentication');

        $params = $request->getParsedBody();
        if (!isset($params['group_number']) || empty($params['group_number'])) {
            throw new ValidateException('group_number');
        }
        if (!isset($params['uid']) || empty($params['uid'])) {
            throw new ValidateException('uid');
        }

        $redis = \Hyperf\Utils\ApplicationContext::getContainer()->get(\Hyperf\Redis\Redis::class);
        $uid = $redis->get($token);

        //查询执行者角色
        $roles = GroupMember::where(['uid' => $uid, 'group_number' => $params['group_number']])->value('type');

        $roleArr = [
            'leader', 'admin'
        ];
        if (!array_search($roles, $roleArr)) {
            throw new BusinessException('您没有权限进行操作');
        }

        //查询被执行这角色
        $groupMemberRole = GroupMember::where(['uid' => $params['uid'], 'group_number' => $params['group_number']])->value('type');
        if (array_search($groupMemberRole, $roleArr)) {
            throw new BusinessException('您没有权限进行操作');
        }

        return $handler->handle($request);
    }
}