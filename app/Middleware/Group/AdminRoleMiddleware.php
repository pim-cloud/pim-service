<?php

declare(strict_types=1);

namespace App\Middleware\Group;

use App\Model\GroupMember;
use Hyperf\Di\Annotation\Inject;
use App\Exception\BusinessException;
use App\Exception\ValidateException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AdminRoleMiddleware implements MiddlewareInterface
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
        if (!isset($params['group_number']) && empty($params['group_number'])) {
            throw new ValidateException('group_number  must ');
        }

        $redis = \Hyperf\Utils\ApplicationContext::getContainer()->get(\Hyperf\Redis\Redis::class);
        $uid = $redis->get($token);

        $groupMembers = GroupMember::where(['uid' => $uid, 'group_number' => $params['group_number']])->first();

        if (!$groupMembers) {
            throw new BusinessException('未查询到群组');
        }
        if ($groupMembers->type <> 'admin') {
            throw new BusinessException('您没有权限进行操作');
        }
        return $handler->handle($request);
    }
}