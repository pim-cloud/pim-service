<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Group;
use App\Model\Member;
use App\Model\MessageSessionList;
use Hyperf\Utils\Context;
use App\Model\GroupMember;
use Overtrue\Pinyin\Pinyin;
use App\Model\ContactsFriend;
use App\Model\ContactsAddRecord;
use App\Exception\BusinessException;

class ContactsService
{

    /**
     * 搜索用户或者群
     * @param string $acceptType
     * @param string $keyword
     * @return \Hyperf\Database\Model\Builder
     */
    public function searchService(string $acceptType, string $keyword)
    {
        if ($acceptType === 'personal') {
            $members = Member::whereRaw("(concat(username,code,nickname,email) like '%" . $keyword . "%')")->get();
            if ($members) {
                foreach ($members as $key => $val) {
                    if ($val->code === Context::get('code')) {
                        unset($members[$key]);
                    }
                    $val->head_image = picturePath($val->head_image);
                }
            }
            return $members;
        }

        return Group::where('group_name', 'like', '%' . $keyword . '%')->get();

    }


    /**
     * 发送添加好友请求
     * @param array $data
     * @return array
     * @throws BusinessException
     */
    public function sendAddFriendRequest(array $data): array
    {
        if (Context::get('code') === $data['accept_code']) {
            throw new BusinessException('不能添加自己');
        }
        $members = Member::where('code', $data['accept_code'])->first();
        if ($members === null) {
            throw new BusinessException('未查询到该用户具体信息');
        }
        //查询是否多次发起请求
        $record = ContactsAddRecord::record(Context::get('code'), $data['accept_code']);
        if ($record && $record->status === 'agree') {
            throw new BusinessException('已经是好友了');
        }
        if ($record && $record->status === 'pending') {
            throw new BusinessException('发送成功，对方处理中');
        }
        $data['message_type'] = 'add_friend';
        $data['main_code'] = Context::get('code');
        $id = enter($data);
        if ($id) {
            ContactsAddRecord::create([
                'main_code' => Context::get('code'),
                'accept_code' => $data['accept_code'],
                'remarks' => $data['remarks'],
                'status' => 'pending',
            ]);
            return ['code' => 200, 'msg' => '发送成功'];
        }
        return ['code' => 200, 'msg' => '发送成功，对方处理中'];
    }

    /**
     * 获取好友请求列表
     * @return mixed
     */
    public function getFriendsRequestList()
    {
        $record = ContactsAddRecord::where('accept_code', Context::get('code'))->get();
        $data = [];
        if (!$record->isEmpty()) {
            foreach ($record as $item) {
                $members = Member::find($item->send_code);
                $data = [
                    [
                        'record_id' => $item->record_id,
                        'send_code' => $item->main_code,
                        'send_head_image' => picturePath($members->head_image),
                        'send_nickname' => $members->nickname,
                        'accept_code' => $item->accept_code,
                        'remarks' => $item->remarks,
                        'status' => $item->status,
                        'created_at' => $item->created_at,
                        'updated_at' => $item->updated_at,
                    ]
                ];
            }
        }

        return $data;
    }

    /**
     * 添加好友请求处理
     * @param array $params
     * @return bool
     * @throws BusinessException
     */
    public function friendRequestProcessing(array $params)
    {
        $status = ['agree', 'refuse', 'ignore'];
        if (!in_array($params['status'], $status)) {
            throw new BusinessException('不支持当前状态:' . $params['status']);
        }
        $record = ContactsAddRecord::find($params['record_id']);

        if ($record != null) {
            switch ($params['status']) {
                case 'agree':
                    //同意
                    goto agree;
                case 'refuse':
                    goto queue;
                case 'ignore':
                    echo '忽略好友请求';
                    break;
                default:
                    throw new BusinessException('不支持当前状态');
            }
            //同意
            agree:
            ContactsFriend::createA($record->main_code, $record->accept_code);
            ContactsFriend::createA($record->accept_code, $record->main_code);
            //查询发送人基础信息
            $member = Member::findFromCache($params['send_code']);
            $params['head_image'] = $member->head_image;
            $params['nickname'] = $member->nickname;

            goto queue;

            //写入消息队列推送事件消息
            queue:

            enter($params);

            $record->status = $params['status'];

            return $record->save();
        }

        return '该申请记录不存在';
    }

    /**
     * 获取好友列表
     * @return array
     */
    public function getContactsList()
    {
        $contactsFriends = ContactsFriend::where('main_code', Context::get('code'))->get();

        $data = [];
        if ($contactsFriends) {
            $friendData = [];
            foreach ($contactsFriends as $k => $item) {
                $members = Member::findFromCache($item->accept_code);
                if ($members) {
                    $friendData[$k]['head_image'] = picturePath($members->head_image);
                    $friendData[$k]['nickname'] = $members->nickname;
                    $friendData[$k]['email'] = $members->email;
                    $friendData[$k]['remarks'] = $item->remarks;
                    $friendData[$k]['code'] = $item->accept_code;
                    $friendData[$k]['type'] = 'personal';
                    $friendData[$k]['initials'] = 'A';
                }
            }
            foreach ($friendData as $item) {
                $k = empty($item['initials']) ? '其他' : $item['initials'];
                $data[$k][] = $item;
            }
            ksort($data, SORT_NATURAL);
        }
        return $data;
    }

    function firstChar($name)
    {
        $el = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
        $str = substr($name, 0, 1);
        if (array_search(strtoupper($str), $el)) {
            return strtoupper($str);
        }
        $pinyin = new Pinyin('\\Overtrue\\Pinyin\\MemoryFileDictLoader');
        $pin = $pinyin->abbr($name);
        $res = strtolower($pin);
        return strtoupper(substr($res, 0, 1));
    }


    /**
     * 查询我拥有的群组
     * @return array
     */
    public function getContactGroups()
    {
        //查询群组
        $groupData = [];
        $groups = GroupMember::where('group_member.m_code', Context::get('code'))
            ->leftJoin('group', 'group.code', '=', 'group_member.code')
            ->select(['group.head_image', 'group.nickname', 'group.code'])
            ->get();
        if ($groups) {
            foreach ($groups as $k => $item) {
                $groupData[$k]['head_image'] = picturePath($item->head_image);
                $groupData[$k]['nickname'] = $item->nickname;
                $groupData[$k]['code'] = $item->code;
                $groupData[$k]['type'] = 'group';
            }
        }

        return $groupData;
    }

    /**
     * 好友编辑
     * @param array $params
     * @return int[]
     * @throws BusinessException
     */
    public function editService(array $params)
    {
        switch ($params['type']) {
            case 'deleteFriend':
                ContactsFriend::doubleDelete(Context::get('code'), $params['acceptCode']);
                break;
            case 'remarks':
                $contacts = ContactsFriend::contacts(Context::get('code'), $params['acceptCode']);
                if ($contacts) {
                    $contacts->remarks = $params['remarks'];
                    $contacts->save();
                }
                break;
            case 'disturb':
                ContactsFriend::setConfig($params['id'], 'disturb', $params['configValue']);
                break;
            case 'star':
                ContactsFriend::setConfig($params['id'], 'star', $params['configValue']);
                break;
        }

        return ['code' => 200];
    }
}