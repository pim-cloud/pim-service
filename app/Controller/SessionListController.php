<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Group;
use App\Model\Member;
use App\Model\Message;
use App\Redis\SessionList;
use Hyperf\Utils\Context;
use App\Model\GroupMember;
use App\Model\ContactsFriend;
use Hyperf\Di\Annotation\Inject;
use App\Model\MessageSessionList;
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
        $list = SessionList::getInstance()->sessionLists(Context::get('uid'));
        $data = [];
        if (!empty($list)) {
            foreach ($list as $key => $item) {
                $data[] = json_decode($item);
            }
        }
        return $this->apiReturn($data);
    }

    /**
     * 新增一条会话记录
     * @PostMapping(path="create")
     */
    public function create()
    {
        $params = $this->request->all();
        $validator = $this->validationFactory->make($params, ['session_type' => 'required', 'accept_code' => 'required']);
        if ($validator->fails()) {
            throw new ValidateException($validator->errors()->first());
        }
        //是否在列表中存在
        $sessions = SessionList::getInstance()->getSessionAfield(Context::get('uid'), $params['accept_code']);
        if ($sessions) {
            return $this->apiReturn();
        }

        if ($params['session_type'] === 'personal') {
            //判断是否是双向好友
            $friend = ContactsFriend::twoWayFriend(Context::get('uid'), $params['accept_code']);
            if (!$friend) {
                return $this->apiReturn(['code' => 202, 'msg' => '不是双向好友，不可以发信息']);
            }
        }
        if ($params['session_type'] === 'group') {
            //判断是否是群成员
            $member = GroupMember::where('group_number', $params['accept_code'])
                ->where('uid', Context::get('uid'))->first();
            if (!$member) {
                return $this->apiReturn(['code' => 202, 'msg' => '不是群成员，不可以发信息']);
            }
        }


        //查询最后消息，时间
        $message = Message::lastMsg((string)$params['accept_code'], Context::get('uid'));
        if ($message) {
            $session = MessageSessionList::create([
                'session_type' => $params['session_type'],
                'uid' => Context::get('uid'),
                'accept_uid' => $params['accept_code'],
                'last_time' => $message->created_at,
                'last_message' => $message->content,
                'last_message_type' => $message->content_type,
            ]);
        } else {
            $session = MessageSessionList::create([
                'session_type' => $params['session_type'],
                'uid' => Context::get('uid'),
                'accept_uid' => $params['accept_code'],
            ]);
        }
        if ($params['session_type'] === 'group') {
            $accept = Group::findFromCache($params['accept_code']);
            $nickname = $accept->group_name;
            $headImage = $accept->group_head_image;
        } else {
            $accept = Member::findFromCache($params['accept_code']);
            $nickname = $accept->nickname;
            $headImage = $accept->head_image;
        }

        $data = [
            'accept_code' => $params['accept_code'],
            'accept_info' => [
                'nickname' => $nickname,
                'head_image' => picturePath($headImage)
            ],
            'disturb_status' => $session->disturb_status,
            'unread' => 0,
            'last_message' => isset($message->content) ?$message->content: '',
            'last_message_type' => isset($message->content_type) ?$message->content_type: '',
            'last_time' => isset($message->created_at) ?$message->created_at: '',
            'session_id' => $session->session_id,
            'session_type' => $params['session_type'],
            'topping' => $session->topping,
        ];

        //添加会话列表
        SessionList::getInstance()->addSession(Context::get('uid'), $params['accept_code'], $data);

        return $this->apiReturn($data);
    }

    /**
     * 删除一条会话记录
     * @PostMapping(path="delete")
     */
    public function delete()
    {
        $validator = $this->validationFactory->make($this->request->all(), ['sessionId' => 'required']);
        if ($validator->fails()) {
            throw new ValidateException($validator->errors()->first());
        }
        $session = MessageSessionList::find($validator->validated()['sessionId']);
        if ($session) {
            SessionList::getInstance()->delSession(Context::get('uid'), $session->accept_uid);
            $session->delete();
        }
        return $this->apiReturn(['code' => 200, 'msg' => '删除成功']);
    }

    /**
     * 是否开启或关闭免打扰
     * @PostMapping(path="setDisturb")
     */
    public function setDisturb()
    {
        $validator = $this->validationFactory->make($this->request->all(), ['acceptUid' => 'required']);
        if ($validator->fails()) {
            throw new ValidateException($validator->errors()->first());
        }
        $save = MessageSessionList::saveDisturbStatus(Context::get('uid'), $validator->validated()['acceptUid']);

        return $this->apiReturn($save);
    }

    /**
     * 更新消息未读条数
     * @PostMapping(path="saveSessionUnread")
     */
    public function saveSessionUnread()
    {
        return $this->apiReturn();
    }

    /**
     * 会话置顶
     * @PostMapping(path="sessionTopping")
     */
    public function sessionTopping()
    {
        $validator = $this->validationFactory->make($this->request->all(), ['sessionId' => 'required']);
        if ($validator->fails()) {
            throw new ValidateException($validator->errors()->first());
        }
        $topping = MessageSessionList::sessionTop($validator->validated()['sessionId']);

        return $this->apiReturn($topping);
    }
}