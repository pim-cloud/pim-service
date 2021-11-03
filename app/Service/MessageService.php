<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Group;
use App\Model\Member;
use App\Model\Message;
use Hyperf\Utils\Context;
use App\Model\GroupMember;
use App\Model\MessageIndex;
use Hyperf\DbConnection\Db;
use App\Exception\BusinessException;

class MessageService
{
    const SINGLE_CHAT = 'single_chat';

    /**
     * 发送消息
     * @param array $request
     * @return string
     * @throws BusinessException
     */
    public function sendMessageService(array $request)
    {
        $request['created_at'] = date('Y-m-d:H:i:s');
        $request['content'] = htmlspecialchars_decode($request['content']);
        //查询发送人信息
        if ($request['accept_type'] === 'group') {
            $groups = Group::find($request['accept_uid']);
            $request['head_image'] = $groups->group_head_image;
            $request['nikename'] = $groups->group_name;
        }

        $members = Member::find($request['send_uid']);

        $request['send_head_image'] = $members->head_image;
        $request['send_nikename'] = $members->nikename;

        $id = enter($request);

        if (!$id) {
            throw new BusinessException('发送失败，请重试');
        }

        $message = new Message();
        $messageIndex = new MessageIndex();

        Db::beginTransaction();
        try {
            $message->msg_id = $id;
            $message->content = $request['content'];
            $message->send_uid = $request['send_uid'];
            $message->accept_type = $request['accept_type'];
            $message->accept_uid = $request['accept_uid'];
            $message->content_type = $request['content_type'];
            $message->save();

            //如果是群组，给每个群成员维护一个消息列表
            if ($request['accept_type'] === 'group') {
                //查询当前群组成员
                $groupNumber = $request['accept_uid'];
                $group = GroupMember::where('group_number', $groupNumber)->get();
                foreach ($group as $item) {
                    $messageIndex->send_uid = $request['send_uid'];
                    $messageIndex->accept_uid = $item->uid;
                    $messageIndex->msg_id = $id;
                    $messageIndex->read_state = 'unread';
                    $messageIndex->save();
                }
            } else {
                $messageIndex->send_uid = Context::get('uid');
                $messageIndex->accept_uid = $request['accept_uid'];
                $messageIndex->msg_id = $id;
                $messageIndex->read_state = 'unread';
                $messageIndex->save();
            }

            Db::commit();

            return ['msg_id' => $id];

        } catch (\Throwable $e) {
            Db::rollBack();
            throw new BusinessException('发送失败' . $e->getMessage());
        }


    }


    /**
     * 消息ack
     * @param string $msgId
     * @return string
     * @throws BusinessException
     */
    public function ackService(string $msgId)
    {
        if (ack($msgId)) {
            $msg = MessageIndex::where('msg_id', $msgId)->first();
            if ($msg) {
                $msg->read_state = 'read';
                $msg->save();
            }
            return 'success';
        }
        throw new BusinessException('ack error');
    }


    /**
     * 查询聊天记录
     * @param array $request
     * @return array|array[]
     */
    public function getMsgRecordService(array $request)
    {
        $where[] = ['send_uid', Context::get('uid')];
        $where[] = ['accept_uid', $request['accept_code']];

        //如果本地存在最后一条聊天记录id
        /*if (isset($request['last_msg_id']) && !empty($request['last_msg_id'])) {
            $where[] = ['msg_id', '<', $request['last_msg_id']];
        }*/

        $data = [];

        $listModel = MessageIndex::query()->with('messageOne')
            ->where($where)->orWhere([
                ['send_uid', $request['accept_code']],
                ['accept_uid', Context::get('uid')]
            ]);

        $count = $listModel->count();//总条数

        $list = $listModel->forPage((int)$request['page'], (int)$request['perPage'])->get();

        if ($list) {

            foreach ($list as $item) {
                $item->content = $item->messageOne->content;
                $item->content_type = $item->messageOne->content_type;
                unset($item->messageOne);
                unset($item->updated_at);
            }

            $send = Member::find(Context::get('uid'));

            if ($request['session_type'] === 'group') {
                $group = Group::findFromCache($request['accept_code']);
                $accept = [
                    'uid' => $group->group_number,
                    'nikename' => $group->group_name,
                    'head_image' => $group->group_head_image,
                ];
            } else {
                $member = Member::findFromCache($request['accept_code']);
                $accept = [
                    'uid' => $member->uid,
                    'nikename' => $member->nikename,
                    'head_image' => $member->head_image,
                ];
            }

            $data['count'] = $count;
            $data['send'] = [
                'uid' => $send->uid,
                'nikename' => $send->nikename,
                'head_image' => $send->head_image,
            ];
            $data['accept'] = $accept;
            $data['lists'] = $list;

        }
        return $data;
    }
}