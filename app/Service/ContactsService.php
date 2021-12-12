<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Group;
use App\Model\Member;
use Hyperf\Utils\Context;
use App\Model\GroupMember;
use Overtrue\Pinyin\Pinyin;
use App\Model\ContactsFriend;
use App\Model\ContactsAddRecord;
use App\Exception\BusinessException;
use Hyperf\Paginator\LengthAwarePaginator;

class ContactsService
{

    /**
     * 查询符合条件的用户列表
     * @param string $keyword 模糊条件
     * @param int $currentPage 当前页
     * @param int $perPage 总条数
     * @return LengthAwarePaginator
     */
    public function searchUsersLists(string $keyword, int $currentPage, int $perPage)
    {
        $members = Member::where('username', 'like', '%' . $keyword . '%')->get();

        $lengthAwarePaginator = new LengthAwarePaginator($members, $members->count(), $perPage, $currentPage);

        $data = $lengthAwarePaginator->toArray();

        if (isset($data['data']) && !empty($data['data'])) {
            return $data;
        }
        return [];
    }


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
     * @param $data
     * @return array
     * @throws BusinessException
     */
    public function sendAddFriendRequest($data): array
    {
        if (Context::get('code') === $data['accept_code']) {
            throw new BusinessException('不能添加自己');
        }
        $members = Member::where('code', $data['accept_code'])->first();
        if ($members === null) {
            throw new BusinessException('未查询到该用户具体信息');
        }
        //查询是否多次发起请求
        $record = ContactsAddRecord::query()
            ->where([
                ['send_code', $data['send_code']],
                ['accept_code', $data['accept_code']],])->first();

        if ($record && $record->status === 'agree') {
            throw new BusinessException('已经是好友了');
        }
        if ($record && $record->status === 'pending') {
            throw new BusinessException('发送成功，对方处理中');
        }

        $id = enter($data);
        if ($id) {
            $contactsAddRecordModel = new ContactsAddRecord();
            $contactsAddRecordModel->record_id = $id;
            $contactsAddRecordModel->send_code = $data['send_code'];
            $contactsAddRecordModel->accept_code = $data['accept_code'];
            $contactsAddRecordModel->remarks = $data['remarks'];
            $contactsAddRecordModel->status = 'pending';
            $contactsAddRecordModel->save();
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
                        'send_code' => $item->send_code,
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
            $a = ContactsFriend::contacts($record->send_code, $record->accept_code);
            if (!$a) {
                ContactsFriend::create([
                    'remarks' => '',
                    'main_code' => $record->send_code,
                    'friend_code' => $record->accept_code,
                ]);
            }
            $b = ContactsFriend::contacts($record->accept_code, $record->send_code);
            if (!$b) {
                ContactsFriend::create([
                    'main_code' => $record->accept_code,
                    'friend_code' => $record->send_code,
                ]);
            }
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
        if (!$contactsFriends->isEmpty()) {
            $friendData = [];
            foreach ($contactsFriends as $k => $item) {
                $members = Member::findFromCache($item->friend_code);
                if ($members) {
                    $friendData[$k]['head_image'] = picturePath($members->head_image);
                    $friendData[$k]['nickname'] = $members->nickname;
                    $friendData[$k]['email'] = $members->email;
                    $friendData[$k]['remarks'] = $item->remarks;
                    $friendData[$k]['code'] = $item->friend_code;
                    $friendData[$k]['type'] = 'personal';
                    $friendData[$k]['initials'] = $this->firstChar($members->nickname);
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
        $pin = $pinyin->abbr($str);
        return strtolower($pin);
    }


    /**
     * 查询我拥有的群组
     * @return array
     */
    public function getContactGroups()
    {
        var_dump(Context::get('code'));
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
     * @return bool
     */
    public function editService(array $params)
    {
        switch ($params['type']) {
            case 'deleteFriend':
                ContactsFriend::doubleDelete(Context::get('code'), $params['friendCode']);
                break;
            case 'remarks':
                $contacts = ContactsFriend::contacts(Context::get('code'), $params['friendCode']);
                if ($contacts) {
                    $contacts->remarks = $params['remarks'];
                    return $contacts->save();
                }
                break;
        }
    }
}