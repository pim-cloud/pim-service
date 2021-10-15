<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Member;
use Hyperf\Utils\Context;
use App\Middleware\Auth\AuthMiddleware;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middleware;

/**
 * @Controller(prefix="member")
 * @Middleware(AuthMiddleware::class)
 */
class MemberController extends AbstractController
{
    /**
     * 获取用户信息，默认查询登录用户的信息
     * @GetMapping(path="getMemberInfo")
     */
    public function getMemberInfo()
    {
        $uid = $this->request->query('uid');
        if (empty($uid)) {
            $uid = Context::get('uid');
        }
        $member = Member::findFromCache($uid);
        if (!empty($member)) {
            unset($member['password']);
            unset($member['salt']);
        }
        return $this->apiReturn($member);
    }
}