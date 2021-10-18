<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\Di\Annotation\Inject;
use App\Service\SessionListService;
use App\Exception\ValidateException;
use App\Middleware\Auth\AuthMiddleware;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;

/**
 * 会话
 * @Controller(prefix="session")
 * @Middleware(AuthMiddleware::class)
 */
class SessionListController extends AbstractController
{

    /**
     * @Inject()
     * @var ValidatorFactoryInterface
     */
    protected $validationFactory;

    /**
     * 获取会话列表
     * @GetMapping(path="getSessionList")
     */
    public function getSessionList()
    {
        return $this->apiReturn((new SessionListService())->sessionListService());
    }

    /**
     * 新增一条会话记录
     * @PostMapping(path="create")
     */
    public function create()
    {
        $validator = $this->validationFactory->make($this->request->all(),
            [
                'session_type' => 'required',
                'code' => 'required',
            ]
        );
        if ($validator->fails()) {
            throw new ValidateException($validator->errors()->first());
        }
        return $this->apiReturn((new SessionListService())->createSessionService($validator->validated()));
    }

    /**
     * 删除一条会话记录
     * @PostMapping(path="delete")
     */
    public function delete()
    {
        $validator = $this->validationFactory->make($this->request->all(),
            [
                'sessionId' => 'required',
            ]
        );
        if ($validator->fails()) {
            throw new ValidateException($validator->errors()->first());
        }
        return $this->apiReturn((new SessionListService())->deleteSessionService((int)$validator->validated()['sessionId']));
    }

    /**
     * 是否开启或关闭免打扰
     * @PostMapping(path="setDisturb")
     */
    public function setDisturb()
    {
        $validator = $this->validationFactory->make($this->request->all(),
            [
                'acceptUid' => 'required',
            ]
        );
        if ($validator->fails()) {
            throw new ValidateException($validator->errors()->first());
        }
        return $this->apiReturn((new SessionListService())->setDisturbService((string)$validator->validated()['acceptUid']));
    }

    /**
     * 更新消息未读条数
     * @PostMapping(path="saveSessionUnread")
     */
    public function saveSessionUnread()
    {
        return $this->apiReturn((new SessionListService())->saveSessionUnreadService());
    }

    /**
     * 会话置顶
     * @PostMapping(path="sessionTopping")
     */
    public function sessionTopping()
    {
        $validator = $this->validationFactory->make($this->request->all(),
            [
                'sessionId' => 'required',
            ]
        );
        if ($validator->fails()) {
            throw new ValidateException($validator->errors()->first());
        }
        return $this->apiReturn((new SessionListService())->sessionToppingService((int)$validator->validated()['sessionId']));
    }
}