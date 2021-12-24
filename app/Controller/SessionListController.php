<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Group;
use App\Model\Member;
use App\Model\Message;
use App\Redis\SessionList;
use App\Service\SessionService;
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
        $list = SessionList::getInstance()->sessionLists(Context::get('code'));
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
        $validator = $this->validationFactory->make($params, ['sessionType' => 'required', 'acceptCode' => 'required']);
        if ($validator->fails()) {
            throw new ValidateException($validator->errors()->first());
        }
        //是否在列表中存在
        $sessions = SessionList::getInstance()->getSessionAfield(Context::get('code'), $params['acceptCode']);
        if ($sessions) {
            return $this->apiReturn(json_decode($sessions, true));
        }

        if ($params['sessionType'] === 'personal') {
            //判断是否是双向好友
            $friend = ContactsFriend::doubleFriend(Context::get('code'), $params['acceptCode']);
            if (!$friend) {
                return $this->apiReturn(['code' => 202, 'msg' => '不是双向好友，不可以发信息']);
            }
        }
        if ($params['sessionType'] === 'group') {
            //判断是否是群成员
            $member = GroupMember::where('code', $params['acceptCode'])
                ->where('m_code', Context::get('code'))->first();
            if (!$member) {
                return $this->apiReturn(['code' => 202, 'msg' => '不是群成员，不可以发信息']);
            }
        }


        //查询最后消息，时间
        $message = Message::lastMsg(Context::get('code'), (string)$params['acceptCode']);
        if ($message) {
            $session = MessageSessionList::create([
                'session_type' => $params['sessionType'],
                'main_code' => Context::get('code'),
                'accept_code' => $params['acceptCode'],
                'last_time' => $message->created_at,
                'last_message' => $message->content,
                'last_message_type' => $message->content_type,
            ]);
        } else {
            $session = MessageSessionList::create([
                'session_type' => $params['sessionType'],
                'main_code' => Context::get('code'),
                'accept_code' => $params['acceptCode'],
            ]);
        }

        $initials = '';
        if ($params['sessionType'] === 'group') {
            $accept = Group::findFromCache($params['acceptCode']);
            $nickname = $accept->nickname;
            $headImage = $accept->head_image;
        } else {
            $accept = Member::findFromCache($params['acceptCode']);
            $nickname = $accept->nickname;
            $headImage = $accept->head_image;
            $initials = 'A';
        }

        //消息是否不提示
        $disturb = 0;
        if ($params['sessionType'] === 'personal') {
            $contacts = ContactsFriend::contacts(Context::get('code'), $params['acceptCode']);
            $disturb = $contacts ? $contacts->disturb : 0;
        }

        $data = [
            'accept_code' => $params['acceptCode'],
            'accept_info' => [
                'remarks' => $params['remarks'],
                'nickname' => $nickname,
                'head_image' => picturePath($headImage),
                'initials' => $initials
            ],
            'disturb' => $disturb,
            'topping' => $session->topping,
            'unread' => 0,
            'last_message' => isset($message->content) ? $message->content : '',
            'last_message_type' => isset($message->content_type) ? $message->content_type : '',
            'last_time' => isset($message->created_at) ? $message->created_at : '',
            'session_id' => $session->session_id,
            'session_type' => $params['sessionType'],
        ];

        //添加会话列表
        SessionList::getInstance()->addSession(Context::get('code'), $params['acceptCode'], $data);

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
            SessionList::getInstance()->delSession(Context::get('code'), $session->accept_code);
            $session->delete();
        }
        return $this->apiReturn(['code' => 200, 'msg' => '删除成功']);
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
        $topping = MessageSessionList::find($validator->validated()['sessionId']);
        if ($topping) {
            $topping->topping === 1 ? $topping->topping = 0 : $topping->topping = 1;
            $topping->save();
        }
        return $this->apiReturn($topping);
    }

    /**
     * 会话编辑
     * @PostMapping(path="editSession")
     */
    public function editSession()
    {
        $params = $this->request->all();
        $validator = $this->validationFactory->make($params, [
            'type' => 'required',
            'sessionId' => 'required',
        ]);
        if ($validator->fails()) {
            throw new ValidateException($validator->errors()->first());
        }
        return $this->apiReturn((new SessionService())->sessionEditService($params));
    }
}