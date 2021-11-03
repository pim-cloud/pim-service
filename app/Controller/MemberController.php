<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Member;
use Hyperf\Utils\Context;
use Hyperf\Di\Annotation\Inject;
use App\Exception\ValidateException;
use App\Middleware\Auth\AuthMiddleware;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;

/**
 * @Controller(prefix="member")
 * @Middleware(AuthMiddleware::class)
 */
class MemberController extends AbstractController
{

    /**
     * @Inject
     * @var ValidatorFactoryInterface
     */
    protected $validationFactory;

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

    /**
     * 修改密码
     * @PostMapping(path="updateP")
     */
    public function updateP()
    {
        $validator = $this->validationFactory->make($this->request->all(), [
            'oldpwd' => 'required',
            'newpwd' => 'required',
        ]);
        if ($validator->fails()) {
            throw new ValidateException($validator->errors()->first());
        }
        $data = $validator->validated();

        $saveP = Member::saveP(Context::get('uid'), $data['oldpwd'], $data['newpwd']);

        if ($saveP) return $this->apiReturn();

        return $this->apiReturn(['code' => 202, 'msg' => '原密码错误']);
    }
}