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
        if (!$members->isEmpty()) {
            foreach ($members as $key => $val) {
                unset($val['password']);
                unset($val['salt']);
            }
        }
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
            $members = Member::where('username', 'like', '%' . $keyword . '%')->get();
            if ($members) {
                foreach ($members as $key => $val) {
                    if ($val->uid === Context::get('uid')) {
                        unset($members[$key]);
                    }
                    unset($val['password']);
                    unset($val['salt']);
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
        if (Context::get('uid') === $data['accept_uid']) {
            throw new BusinessException('不能添加自己');
        }
        $members = Member::where('uid', $data['accept_uid'])->first();
        if ($members === null) {
            throw new BusinessException('未查询到该用户具体信息');
        }
        //查询是否多次发起请求
        $record = ContactsAddRecord::query()
            ->where([
                ['send_uid', $data['send_uid']],
                ['accept_uid', $data['accept_uid']],])->first();

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
            $contactsAddRecordModel->send_uid = $data['send_uid'];
            $contactsAddRecordModel->accept_uid = $data['accept_uid'];
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
        $record = ContactsAddRecord::where('accept_uid', Context::get('uid'))->get();
        $data = [];
        if (!$record->isEmpty()) {
            foreach ($record as $item) {
                $members = Member::find($item->send_uid);
                $data = [
                    [
                        'record_id' => $item->record_id,
                        'send_uid' => $item->send_uid,
                        'send_head_image' => $members->head_image,
                        'send_nikename' => $members->nikename,
                        'accept_uid' => $item->accept_uid,
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
            $a = ContactsFriend::query()
                ->where([
                    ['main_uid', $record->send_uid],
                    ['friend_uid', $record->accept_uid],
                ])->first();
            if (!$a) {
                ContactsFriend::create([
                    'main_uid' => $record->send_uid,
                    'friend_uid' => $record->accept_uid,
                ]);
            }
            $b = ContactsFriend::query()
                ->where([
                    ['main_uid', $record->accept_uid],
                    ['friend_uid', $record->send_uid],
                ])->first();
            if (!$b) {
                ContactsFriend::create([
                    'main_uid' => $record->accept_uid,
                    'friend_uid' => $record->send_uid,
                ]);
            }
            //查询发送人基础信息
            $member = Member::find($params['send_uid']);
            $params['head_image'] = $member->head_image;
            $params['nikename'] = $member->nikename;

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
        $contactsFriends = ContactsFriend::where('main_uid', Context::get('uid'))->get();

        $data = [];
        if (!$contactsFriends->isEmpty()) {
            $friendData = [];
            foreach ($contactsFriends as $k => $item) {
                $members = Member::findFromCache($item->friend_uid);
                if ($members) {
                    $friendData[$k]['head_image'] = $members->head_image;
                    $friendData[$k]['nikename'] = $members->nikename;
                    $friendData[$k]['uid'] = $item->friend_uid;
                    $friendData[$k]['type'] = 'personal';
                    $friendData[$k]['initials'] = $this->firstChar($members->nikename);
                }
            }
            foreach ($friendData as   $item) {
                $data[$item['initials']][] = $item;
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
     * 查询我拥有的群组和好友
     * @return array
     */
    public
    function getContactGroups()
    {
        //查询群组
        $groupData = [];
        $groups = GroupMember::where('uid', Context::get('uid'))->get();
        if ($groups) {
            foreach ($groups as $k => $item) {
                $group = Group::find($item->group_number);
                if ($group) {
                    $groupData[$k]['head_image'] = $group->group_head_image;
                    $groupData[$k]['nikename'] = $group->group_name;
                    $groupData[$k]['code'] = $group->group_number;
                    $groupData[$k]['type'] = 'group';
                }
            }
        }

        return $groupData;
    }

    /**
     * 删除好友关系
     * @param string $acceptUid
     * @return false|int|mixed
     */
    public function deleteFriends(string $acceptUid)
    {
        ContactsFriend::where('main_uid', Context::get('uid'))
            ->where('friend_uid', $acceptUid)
            ->delete();
        ContactsFriend::where('main_uid', $acceptUid)
            ->where('friend_uid', Context::get('uid'))
            ->delete();
        return true;
    }
}