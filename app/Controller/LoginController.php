<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Member;
use App\Tools\RedisTools;
use App\Request\LoginRequest;
use Qbhy\HyperfAuth\AuthManager;
use Hyperf\Di\Annotation\Inject;
use App\Request\RegisterRequest;
use Qbhy\HyperfAuth\Annotation\Auth;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Contract\ResponseInterface;

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
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @Inject
     * @var RedisTools
     */
    protected $redisTools;

    /**
     * @PostMapping(path="login")
     */
    public function login(LoginRequest $request)
    {
        $params = $request->validated();
        $member = Member::select(['uid', 'salt', 'username', 'head_image', 'nikename', 'autograph', 'password'])
            ->where('username', $params['username'])
            ->first();
        if ($member && $member->password === md5($params['password'] . $member->salt)) {
            return $this->apiReturn([
                'token' => $this->auth->guard('sso')->login($member, [], 'web')
            ]);
        }
        return ['code' => 0, 'msg' => '账号或密码错误'];
    }

    /**
     * @PostMapping(path="register")
     */
    public function register(RegisterRequest $request)
    {
        $params = $request->validated();
        $data = Member::where('username', $params['username'])->first();
        if ($data <> null) {
            return $this->apiReturn(['code' => 0, 'msg' => '账号已存在']);
        }
        $salt = getSnowflakeId();
        $data = Member::create([
            'uid' => getSnowflakeId(),
            'username' => $params['username'],
            'nikename' => $params['nikename'],
            'head_image' => $params['head_image'],
            'password' => md5($params['password'] . $salt),
            'salt' => $salt,
        ]);
        if ($data) {
            return ['code' => 200, 'msg' => '注册成功'];
        }
        return ['code' => 0, 'msg' => '注册失败'];
    }

    /**
     * @Auth("sso")
     * @PostMapping(path="member")
     */
    public function member()
    {
        return $this->auth->guard('sso')->user();
    }


    /**
     * @Auth("sso")
     * @PostMapping(path="logout")
     */
    public function logout()
    {
        $this->auth->guard('sso')->logout();

        return ['code' => 200];
    }
}