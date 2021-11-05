<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Group;
use App\Model\Member;
use App\Model\Message;
use Hyperf\Utils\Context;
use App\Model\GroupMember;
use App\Model\ContactsFriend;
use App\Model\MessageSessionList;

class SessionListService
{
    /**
     * 获取会话列表
     * @return array|\Hyperf\Database\Model\Builder[]|\Hyperf\Database\Model\Collection
     */
    public function sessionListService()
    {
        $session = MessageSessionList::where('uid', Context::get('uid'))->get();
        if (!$session) {
            return [];
        }

        $data = [];

        foreach ($session as $item) {

            if ($item->session_type === 'group') {
                $group = Group::findFromCache($item->accept_uid);
                $info = [
                    'nikename' => $group->group_name,
                    'head_image' => $group->group_head_image,
                ];
            } else {
                $member = Member::findFromCache($item->accept_uid);
                $info = [
                    'nikename' => $member->nikename,
                    'head_image' => picturePath($member->head_image),
                ];
            }

            $data[] = [
                'accept_info' => $info,
                'topping' => $item->topping,
                'last_time' => $item->last_time,
                'session_id' => $item->session_id,
                'accept_code' => $item->accept_uid,
                'session_type' => $item->session_type,
                'last_message' => $item->last_message,
                'disturb_status' => $item->disturb_status,
                'last_message_type' => $item->last_message_type,
            ];
        }
        return $data;
    }

    /**
     * @param array $params
     * @return array
     */
    public function createSessionService(array $params): array
    {
        if ($params['session_type'] === 'personal') {
            //判断是否是双向好友
            $friend = ContactsFriend::twoWayFriend(Context::get('uid'), $params['accept_code']);
            if (!$friend) {
                return ['code' => 0, 'msg' => '不是双向好友，不可以发信息'];
            }
        }

        if ($params['session_type'] === 'group') {
            //判断是否是群成员
            $member = GroupMember::where('group_number', $params['accept_code'])
                ->where('uid', Context::get('uid'))->first();
            if (!$member) {
                return ['code' => 0, 'msg' => '不是群成员，不可以发信息'];
            }
        }

        //查询最后消息，时间
        $message = Message::where('send_uid', $params['accept_code'])
            ->where('accept_uid', Context::get('uid'))
            ->orderBy('created_at', 'desc')
            ->first();

        $session = MessageSessionList::where('uid', Context::get('uid'))
            ->where('accept_uid', $params['accept_code'])
            ->first();
        if (!$session) {
            $session = MessageSessionList::create([
                'session_type' => $params['session_type'],
                'uid' => Context::get('uid'),
                'accept_uid' => $params['accept_code'],
                'last_time' => $message->created_at,
                'last_message' => $message->content,
                'last_message_type' => $message->content_type,
            ]);
        }


        if (!$session) {
            return ['code' => 0, 'msg' => '发起失败'];
        }

        $accept = Member::findFromCache($params['accept_code']);

        $data = [
            'accept_code' => $params['accept_code'],
            'accept_info' => [
                'nikename' => $accept->nikename,
                'head_image' => $accept->head_image
            ],
            'disturb_status' => $session->disturb_status,
            'last_message' => $message->content,
            'last_message_type' => $message->content_type,
            'last_time' => $message->created_at,
            'session_id' => $session->session_id,
            'session_type' => $params['session_type'],
            'topping' => $session->topping,
        ];

        return $data;
    }

    /**
     * 删除一条会话
     * @param int $sessionId
     * @return array|bool|null
     * @throws \Exception
     */
    public function deleteSessionService(int $sessionId)
    {
        $session = MessageSessionList::find($sessionId);
        if ($session) {
            return $session->delete();
        }
        return ['code' => 0, 'msg' => '已经删除过了'];
    }

    /**
     * 开启或关闭免打扰模式
     * @param string $acceptUid
     * @return bool
     */
    public function setDisturbService(string $acceptUid): bool
    {
        return MessageSessionList::saveDisturbStatus(Context::get('uid'), $acceptUid);
    }

    public function saveSessionUnreadService()
    {

    }

    /**
     * 会话置顶
     * @param int $sessionId
     * @return bool
     */
    public function sessionToppingService(int $sessionId): bool
    {
        $session = MessageSessionList::find($sessionId);
        if ($session) {
            $session->topping = 'no';
        } else {
            $session->topping = 'yes';
        }
        return $session->save();
    }
}