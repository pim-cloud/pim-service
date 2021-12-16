<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Group;
use App\Model\Member;
use App\Model\Message;
use App\Redis\SessionList;
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
            $groups = Group::findFromCache($request['accept_code']);
            $request['head_image'] = picturePath($groups->head_image);
            $request['nickname'] = $groups->nickname;
        }

        $members = Member::findFromCache(Context::get('code'));

        $request['send_head_image'] = $members->head_image;
        $request['send_nickname'] = $members->nickname;
        $request['main_code'] = Context::get('code');

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
            $message->main_code = Context::get('code');
            $message->accept_type = $request['accept_type'];
            $message->accept_code = $request['accept_code'];
            $message->content_type = $request['content_type'];
            $message->save();

            //如果是群组，给每个群成员维护一个消息列表
            if ($request['accept_type'] === 'group') {
                //查询当前群组成员
                $groupNumber = $request['accept_code'];
                $group = GroupMember::where('code', $groupNumber)->get();
                foreach ($group as $item) {
                    $messageIndex->accept_code = $item->code;
                }
            } else {
                $messageIndex->accept_code = $request['accept_code'];
            }
            $messageIndex->main_code = Context::get('code');
            $messageIndex->msg_id = $id;
            $messageIndex->read_state = 'unread';
            $messageIndex->save();

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
        $where[] = ['main_code', Context::get('code')];
        $where[] = ['accept_code', $request['acceptCode']];

        //如果本地存在最后一条聊天记录id
        /*if (isset($request['last_msg_id']) && !empty($request['last_msg_id'])) {
            $where[] = ['msg_id', '<', $request['last_msg_id']];
        }*/

        $data = [];

        $listModel = MessageIndex::query()->with('messageOne')
            ->where($where)->orWhere([
                ['main_code', $request['acceptCode']],
                ['accept_code', Context::get('code')]
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

            $send = Member::find(Context::get('code'));

            if ($request['sessionType'] === 'group') {
                $group = Group::findFromCache($request['acceptCode']);
                $accept = [
                    'code' => $group->code,
                    'nickname' => $group->nickname,
                    'head_image' => picturePath($group->head_image),
                ];
            } else {
                $member = Member::findFromCache($request['acceptCode']);
                $accept = [
                    'code' => $member->code,
                    'nickname' => $member->nickname,
                    'head_image' => picturePath($member->head_image),
                ];
            }

            $data['count'] = $count;
            $data['send'] = [
                'code' => $send->code,
                'nickname' => $send->nickname,
                'head_image' => picturePath($send->head_image),
            ];
            $data['accept'] = $accept;
            $data['lists'] = $list;

        }
        return $data;
    }
}