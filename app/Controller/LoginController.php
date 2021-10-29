<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Member;
use Hyperf\Di\Annotation\Inject;
use Qbhy\HyperfAuth\AuthManager;
use App\Exception\ValidateException;
use Qbhy\HyperfAuth\Annotation\Auth;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;

/**
 * @Controller(prefix="login")
 */
class LoginController extends AbstractController
{

    /**
     * @Inject
     * @var AuthManager
     */
    protected $auth;

    /**
     * @Inject
     * @var ValidatorFactoryInterface
     */
    protected $validationFactory;

    /**
     * 登录
     * @PostMapping(path="login")
     */
    public function login()
    {
        $params = $this->request->all();
        if (!isset($params['ciphertext']) || empty($params['ciphertext'])) {
            throw new ValidateException('ciphertext error');
        }

        $decryptedData = json_decode(decrypt($params['ciphertext']), true);

        $validator = $this->validationFactory->make($decryptedData,
            [
                'username' => 'required',
                'password' => 'required',
                'scene' => 'required',
            ]
        );
        if ($validator->fails()) {
            throw new ValidateException($validator->errors()->first());
        }

        $member = Member::query()
            ->select(['uid', 'salt', 'username', 'head_image', 'nikename', 'autograph', 'password'])
            ->where('username', $decryptedData['username'])
            ->first();
        if ($member && $member->password === md5($decryptedData['password'] . $member->salt)) {
            return $this->apiReturn([
                'token' => $this->auth->guard()->login($member, [], $decryptedData['scene'])
            ]);
        }
        return ['code' => 0, 'msg' => '账号或密码错误'];
    }

    /**
     * 注册账号
     * @PostMapping(path="register")
     */
    public function register()
    {
        $params = $this->request->all();
        if (!isset($params['ciphertext']) || empty($params['ciphertext'])) {
            throw new ValidateException('ciphertext error');
        }
        $decryptedData = json_decode(decrypt($params['ciphertext']), true);
        $validator = $this->validationFactory->make($decryptedData,
            [
                'username' => 'required',
                'nikename' => 'required',
                'password' => 'required',
            ]
        );
        if ($validator->fails()) {
            throw new ValidateException($validator->errors()->first());
        }

        $data = Member::where('username', $decryptedData['username'])->first();
        if ($data <> null) {
            return $this->apiReturn(['code' => 0, 'msg' => '账号已存在']);
        }

        $salt = uniqid();

        $data = Member::create([
            'uid' => getSnowflakeId(),
            'username' => $decryptedData['username'],
            'nikename' => $decryptedData['nikename'],
            'head_image' => $decryptedData['head_image'],
            'password' => md5($decryptedData['password'] . $salt),
            'salt' => $salt,
        ]);
        if ($data) {
            return ['code' => 200, 'msg' => '注册成功'];
        }
        return ['code' => 0, 'msg' => '注册失败'];
    }

    /**
     * 退出登录
     * @Auth("sso")
     * @PostMapping(path="logout")
     */
    public function logout()
    {
        $this->auth->guard('sso')->logout();

        return ['code' => 200, 'msg' => '退出成功'];
    }
}