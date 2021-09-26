<?php

declare(strict_types=1);

namespace App\Middleware\Auth;

use App\Exception\AuthException;
use App\Exception\ValidateException;
use Hyperf\Utils\Context;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Hyperf\Di\Annotation\Inject;

class AuthMiddleware implements MiddlewareInterface
{

    /**
     * @Inject()
     * @var ContainerInterface
     */
    protected $container;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $token = $request->getHeaderLine('authentication');
        if (empty($token)) {
            throw new ValidateException('authentication failed');
        }
        $redis = \Hyperf\Utils\ApplicationContext::getContainer()->get(\Hyperf\Redis\Redis::class);
        $uid = $redis->get($token);
        if (empty($uid)) {
            throw new AuthException('请重新登录');
        }
        Context::set('uid', (string)$uid);

        return $handler->handle($request);
    }
}