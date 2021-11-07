<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Member;
use App\Service\FileService;
use Hyperf\Utils\Context;
use Hyperf\Di\Annotation\Inject;
use App\Exception\ValidateException;
use App\Exception\BusinessException;
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
            $member->head_image = picturePath($member->head_image);
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

    /**
     * 修改个性签名
     * @PostMapping(path="updateAutograph")
     */
    public function updateAutograph()
    {
        $params = $this->request->all();
        $validator = $this->validationFactory->make($params, [
            'autograph' => 'required',
        ]);
        if ($validator->fails()) {
            throw new ValidateException($validator->errors()->first());
        }
        $member = Member::findFromCache(Context::get('uid'));
        if (!$member) return $this->apiReturn(['code' => 202, 'msg' => '没查询到用户信息']);

        $member->autograph = $params['autograph'];
        return $this->apiReturn($member->save());
    }

    /**
     * 修改头像
     * @PostMapping(path="updateImage")
     */
    public function updateImage()
    {
        $file = $this->request->file('file');
        if (!$file) {
            throw new BusinessException('文件不存在');
        }
        $fileService = new FileService();
        $filename = $fileService->picture($file);

        $member = Member::findFromCache(Context::get('uid'));
        if (!$member) return $this->apiReturn(['code' => 202, 'msg' => '没查询到用户信息']);

        $member->head_image = $filename;
        $res = $member->save();

        return $this->apiReturn($res);
    }
}