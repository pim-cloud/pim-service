<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Member;
use Hyperf\Di\Annotation\Inject;
use Qbhy\HyperfAuth\AuthManager;
use App\Exception\ValidateException;
use Qbhy\HyperfAuth\Annotation\Auth;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
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

        $validator = $this->validationFactory->make($decryptedData, ['email' => 'required', 'password' => 'required', 'scene' => 'required',]);
        if ($validator->fails()) {
            throw new ValidateException($validator->errors()->first());
        }

        $member = Member::query()
            ->select(['code', 'salt', 'email','username', 'head_image', 'nickname', 'autograph', 'password'])
            ->where('email', $decryptedData['email'])
            ->first();
        var_dump($member);
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
        $validator = $this->validationFactory->make($decryptedData, ['email' => 'required', 'code' => 'required', 'password' => 'required',]);
        if ($validator->fails()) {
            throw new ValidateException($validator->errors()->first());
        }
        $res = redis()->get('verification:code:register:' . $decryptedData['code']);
        if (!$res) {
            return $this->apiReturn(['code' => 202, 'msg' => '验证码错误']);
        }

        $data = Member::where('email', $decryptedData['email'])->first();
        if ($data <> null) {
            return $this->apiReturn(['code' => 0, 'msg' => '邮箱已被注册']);
        }

        $salt = uniqid();

        $data = Member::create([
            'code' => getSnowflakeId(),
            'username' => '',
            'email' => $decryptedData['email'],
            'nickname' => uniqid(),
            'head_image' => 'morentouxiang.png',
            'password' => md5($decryptedData['password'] . $salt),
            'salt' => $salt,
        ]);
        if ($data) {
            return ['code' => 200, 'msg' => '注册成功'];
        }
        return ['code' => 0, 'msg' => '注册失败'];
    }


    /**
     * 获取验证码
     * @GetMapping(path="code")
     */
    public function code()
    {
        $params = $this->request->all();
        if (!isset($params['email']) || empty($params['email'])) {
            throw new ValidateException('email error');
        }
        if (!isset($params['type']) || empty($params['type'])) {
            throw new ValidateException('type error');
        }

        $prefix = '';
        switch ($params['type']) {
            case 'register_code_':
                $prefix = 'verification:code:register:';
                break;
        }

        $code = generateCode();

        redis()->set($prefix . $code, $code, '120');

        asyncQueue()->sendCheckCode([
            'email' => $params['email'],
            'body' => '您正在注册pim系统，您的验证码是: ' . $code . ' 验证码2分钟失效',
        ]);

        return ['code' => 200, 'msg' => '发送成功'];
    }


    /**
     * 退出登录
     * @Auth("sso")
     * @GetMapping(path="logout")
     */
    public function logout()
    {
        $this->auth->guard('sso')->logout();

        return ['code' => 200, 'msg' => '退出成功'];
    }
}