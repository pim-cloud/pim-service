<?php


namespace App\Controller;


use App\Request\CreateGroup;
use App\Service\GroupService;
use Hyperf\Di\Annotation\Inject;
use App\Exception\ValidateException;
use App\Middleware\Auth\AuthMiddleware;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Annotation\GetMapping;
use App\Middleware\Group\LeaderRoleMiddleware;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;

/**
 * @Controller(prefix="group")
 * @Middleware(AuthMiddleware::class)
 */
class GroupController extends AbstractController
{

    /**
     * @Inject
     * @var ValidatorFactoryInterface
     */
    protected $validationFactory;

    /**
     * 创建群组
     * @PostMapping(path="create")
     */
    public function create(CreateGroup $request)
    {
        return $this->apiReturn((new GroupService())->createService($request->validated()));
    }

    /**
     * 群成员编辑
     * @PostMapping(path="leaderTransfer")
     * @Middleware(LeaderRoleMiddleware::class)
     */
    public function groupMemberEdit()
    {
        $validator = $this->validationFactory->make($this->request->all(),
            [
                'group_number' => 'required',
                'uid' => 'required',
                'type' => 'required',
            ]
        );
        if ($validator->fails()) {
            throw new ValidateException($validator->errors()->first());
        }
        return $this->apiReturn((new GroupService())->groupMemberEditService($validator->validated()));
    }

    /**
     * 解散群
     * @PostMapping(path="dissolution")
     * @Middleware(LeaderRoleMiddleware::class)
     */
    public function dissolution()
    {
        $validator = $this->validationFactory->make($this->request->all(),
            [
                'group_number' => 'required',
            ]
        );
        if ($validator->fails()) {
            throw new ValidateException($validator->errors()->first());
        }

        return $this->apiReturn((new GroupService())->dissolutionService((string)$validator->validated()['group_number']));
    }

    /**
     * 获取群组详情
     * @GetMapping(path="getGroupDetail")
     */
    public function getGroupDetail()
    {
        $validator = $this->validationFactory->make($this->request->all(),
            [
                'group_number' => 'required',
            ]
        );
        if ($validator->fails()) {
            throw new ValidateException($validator->errors()->first());
        }

        return $this->apiReturn((new GroupService())->getGroupDetail((string)$validator->validated()['group_number']));
    }

    /**
     * 移除群成员，或者退出群
     * @GetMapping(path="deleteGroupMember")
     */
    public function deleteGroupMember()
    {

    }
}