<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\ContactsFriend;
use App\Model\GroupMember;
use App\Model\Member;
use App\Model\MessageSessionList;
use Hyperf\Utils\Context;

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

        foreach ($session as  $item) {

            $member = Member::findFromCache($item->accept_uid);

            $data[] = [
                'session_id' => $item->session_id,
                'session_type' => $item->session_type,
                'accept_code' => $item->accept_uid,
                'accept_info' => [
                    'head_image' => $member->head_image,
                    'nikename' => $member->nikename,
                ],
                'last_time' => '',
                'last_message' => '',
                'disturb_status' => $item->disturb_status,
                'topping' => $item->topping,
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
            $friend = ContactsFriend::twoWayFriend(Context::get('uid'), $params['code']);
            if (!$friend) {
                return ['code' => 0, 'msg' => '不是双向好友，不可以发信息'];
            }
        }

        if ($params['session_type'] === 'group') {
            //判断是否是群成员
            $member = GroupMember::whetherGroupMember($params['code'], Context::get('uid'));
            if (!$member) {
                return ['code' => 0, 'msg' => '不是群成员，不可以发信息'];
            }
        }

        $session = MessageSessionList::where('uid', Context::get('uid'))
            ->where('accept_uid', $params['code'])->first();
        if (!$session) {
            $session = MessageSessionList::create([
                'session_type' => $params['session_type'],
                'uid' => Context::get('uid'),
                'accept_uid' => $params['code'],
            ]);
        }


        if (!$session) {
            return ['code' => 0, 'msg' => '发起失败'];
        }

        $data = [
            'session_id' => $session->session_id,
            'session_type' => $params['session_type'],
            'uid' => Context::get('uid'),
            'accept_uid' => $params['code'],
            'disturb_status' => 'no',
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