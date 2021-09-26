<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Member;
use App\Tools\RedisTools;
use App\Request\LoginRequest;
use Hyperf\Di\Annotation\Inject;
use App\Request\RegisterRequest;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Contract\ResponseInterface;

/**
 * @Controller(prefix="login")
 */
class LoginController extends AbstractController
{
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
     * @PostMapping(path="login", methods="post")
     */
    public function login(LoginRequest $request)
    {
        $params = $request->validated();

        $data = Member::select(['uid', 'salt', 'username', 'head_image', 'nikename', 'autograph'])
            ->where('username', $params['username'])
            ->where('password', $params['password'])
            ->first();

        if ($data) {
            $token = $this->setToken($data->toArray());
            return $this->apiReturn(['token' => $token]);
        }

        return $this->apiReturn(['code' => 0, 'msg' => '账号或者密码错误']);
    }

    /**
     * @PostMapping(path="register", methods="post")
     */
    public function register(RegisterRequest $request)
    {
        $params = $request->validated();
        $data = Member::where('username', $params['username'])->first();
        if ($data != null) {
            return $this->apiReturn(['code' => 0, 'msg' => '账号已存在']);
        }
        $memberModel = new Member();
        $memberModel->uid = getSnowflakeId();
        $memberModel->username = $params['username'];
        $memberModel->nikename = $params['nikename'];
        $memberModel->head_image = 'http://cdn.jksusu.cn/A.jpg';
        $memberModel->salt = substr(uniqid(), 0, 4);
        $memberModel->password = $params['password'];
        if (!$memberModel->save()) {
            return $this->apiReturn(['code' => 0, 'msg' => '注册失败']);
        }
        return $this->apiReturn();
    }


    public function setToken(array $data): string
    {
        $token = md5($data['uid'] . $data['salt']);
        $this->redisTools->setTokenMappingUid((string)$token,(string)$data['uid']);//token映射uid
        $this->redisTools->setUidMappingMember((string)$data['uid'], $data);//uid映射用户信息
        return $token;
    }

    /**
     * @GetMapping(path="index")
     */
    public function index()
    {
        return 'you are sb!!';
    }
}