<?php
declare(strict_types=1);

namespace App\Middleware\Auth;

use Qbhy\HyperfAuth\AuthManager;
use Hyperf\Di\Annotation\Inject;
use App\Exception\AuthException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthMiddlewares extends AuthManager implements MiddlewareInterface
{
    /**
     * @Inject
     * @var AuthManager
     */
    protected $auth;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!isset($request->getQueryParams()['token']) && empty($request->getQueryParams()['token'])) {
            throw new AuthException('token 验证未通过');
        }
        $token = $request->getQueryParams()['token'];
        $member = $this->auth->getPayload($token);
        if (isset($member['exp']) && $member['exp'] <= time()) {
            throw new AuthException('token 已过期');
        }
        //获取当前渠道token是否一致
        $webToken = redis()->hGet('u:token:' . $member['uid'], 'web');
        if ($webToken != $token) {
            throw new AuthException('当前token不能用在web端登录');
        }
        return $handler->handle($request);
    }
}