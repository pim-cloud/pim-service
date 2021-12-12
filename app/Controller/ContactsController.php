<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\ContactsFriend;
use App\Service\ContactsService;
use Hyperf\Di\Annotation\Inject;
use App\Exception\ValidateException;
use App\Middleware\Auth\AuthMiddleware;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\Utils\Context;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;

/**
 * 联系人模块
 * @Controller(prefix="contacts")
 * @Middleware(AuthMiddleware::class)
 */
class ContactsController extends AbstractController
{

    /**
     * @Inject()
     * @var ValidatorFactoryInterface
     */
    protected $validationFactory;


    /**
     * 搜索符合条件的用户
     * @GetMapping(path="searchUsers")
     */
    public function searchUsers()
    {
        $validator = $this->validationFactory->make($this->request->all(),
            [
                'keyword' => 'required',
                'currentPage' => 'required',
                'perPage' => 'required',
            ]
        );
        if ($validator->fails()) {
            throw new ValidateException($validator->errors()->first());
        }
        $params = $validator->validated();
        return $this->apiReturn((new ContactsService())->searchUsersLists((string)$params['keyword'], (int)$params['currentPage'], (int)$params['perPage']));
    }

    /**
     * 搜索符合条件的用户
     * @GetMapping(path="search")
     */
    public function search()
    {
        $validator = $this->validationFactory->make($this->request->all(),
            [
                'accept_type' => 'required',
                'keyword' => 'required',
            ]
        );
        if ($validator->fails()) {
            throw new ValidateException($validator->errors()->first());
        }
        $params = $validator->validated();
        return $this->apiReturn((new ContactsService())->searchService((string)$params['accept_type'], (string)$params['keyword']));
    }

    /**
     * 发送添加好友请求
     * @PostMapping(path="sendAddFriendRequest")
     */
    public function sendAddFriendRequest()
    {
        $validator = $this->validationFactory->make($this->request->all(),
            ['message_type' => 'required', 'send_code' => 'required', 'accept_code' => 'required', 'remarks' => 'required']
        );
        if ($validator->fails()) {
            throw new ValidateException($validator->errors()->first());
        }
        return $this->apiReturn((new ContactsService())->sendAddFriendRequest($validator->validated()));
    }

    /**
     * 获取好友申请列表
     * @GetMapping(path="getFriendsRequestList")
     */
    public function getFriendsRequestList()
    {
        return $this->apiReturn((new ContactsService())->getFriendsRequestList());
    }

    /**
     * 添加好与请求处理
     * @PostMapping(path="friendRequestProcessing")
     */
    public function friendRequestProcessing()
    {
        $validator = $this->validationFactory->make($this->request->all(), [
            'send_code' => 'required',
            'accept_code' => 'required',
            'message_type' => 'required',
            'record_id' => 'required',
            'status' => 'required',
        ]);
        if ($validator->fails()) {
            throw new ValidateException($validator->errors()->first());
        }
        return $this->apiReturn((new ContactsService())->friendRequestProcessing($validator->validated()));
    }


    /**
     * 获取联系人列表
     * @GetMapping(path="getContactsList")
     */
    public function getContactsList()
    {
        return $this->apiReturn((new ContactsService())->getContactsList());
    }

    /**
     * 获取群组列表
     * @GetMapping(path="getContactGroups")
     */
    public function getContactGroups()
    {
        return $this->apiReturn((new ContactsService())->getContactGroups());
    }


    /**
     * 联系人设置
     * @PostMapping(path="edit")
     */
    public function edit()
    {
        $params = $this->request->all();
        $validator = $this->validationFactory->make($params, [
            'type' => 'required',
            'friendCode' => 'required',
        ]);
        if ($validator->fails()) {
            throw new ValidateException($validator->errors()->first());
        }
        return $this->apiReturn((new ContactsService())->editService($params));
    }

    /**
     * 获取好友详情
     * @GetMapping(path="getFriendDetail")
     */
    public function getFriendDetail()
    {
        $validator = $this->validationFactory->make($this->request->all(), ['code' => 'required']);
        if ($validator->fails()) {
            throw new ValidateException($validator->errors()->first());
        }
        return $this->apiReturn(ContactsFriend::friendDetail(Context::get('code'), $validator->validated()['code']));
    }
}