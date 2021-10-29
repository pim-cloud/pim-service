<?php

declare(strict_types=1);

namespace App\Middleware\Auth;

use Hyperf\Utils\Context;
use Qbhy\HyperfAuth\AuthManager;
use Hyperf\Di\Annotation\Inject;
use App\Exception\AuthException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthMiddleware extends AuthManager implements MiddlewareInterface
{
    /**
     * @Inject
     * @var AuthManager
     */
    protected $auth;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->auth->check('Authorization be overdue');
        $member = $this->auth->user();
        if (empty($member)) {
            throw new AuthException('user null');
        }
        Context::set('uid', (string)$member->uid);
        return $handler->handle($request);
    }
}