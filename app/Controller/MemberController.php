<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Member;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use App\Middleware\Auth\AuthMiddleware;
use Hyperf\Utils\Context;

/**
 * @Controller(prefix="member")
 * @Middleware(AuthMiddleware::class)
 */
class MemberController extends AbstractController
{
    /**
     * 获取用户信息
     * @GetMapping(path="getMemberInfo")
     */
    public function getMemberInfo()
    {
        $uid = '';
        if (!empty($this->request->query('uid'))) {
            $uid = $this->request->query('uid');
        } else {
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