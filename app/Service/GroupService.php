<?php

namespace App\Service;

use App\Model\Group;
use App\Model\Member;
use Hyperf\Utils\Context;
use App\Model\GroupMember;
use Hyperf\DbConnection\Db;
use App\Exception\BusinessException;

class GroupService
{
    /**
     * 创建群组
     * @param array $params
     * @return string[]
     * @throws BusinessException
     */
    public function createService(array $params)
    {
        //实现一个分布式锁，防止群号重复
        //$redis = $this->container->get(\Hyperf\Redis\Redis::class);

        $groupNumber = time();

        $groupModel = new Group();
        $groupMemberModel = new GroupMember();

        Db::beginTransaction();
        try {

            //创建群组
            $groupModel->group_number = $groupNumber;
            $groupModel->group_name = $params['group_name'];
            $groupModel->group_head_image = $params['group_head_image'];
            $groupModel->introduction = '这是一个有趣的群';
            $groupModel->save();

            //添加群主
            GroupMember::create([
                'group_number' => $groupNumber,
                'uid' => Context::get('uid'),
                'type' => 'leader',
            ]);

            foreach ($params['group_member'] as $k => $v) {
                GroupMember::create([
                    'group_number' => $groupNumber,
                    'uid' => $v,
                    'type' => 'member',
                ]);
            }

            Db::commit();

            //推送创建群聊消息
            enter([
                'send_uid' => Context::get('uid'),
                'accept_uid' => $groupNumber,
                'message_type' => 'create_group',
                'head_image' => $params['group_head_image'],
                'nickname' => $params['group_name'],
            ]);


            return ['code' => 200, 'msg' => '创建成功', 'data' => ['groupNumber' => $groupNumber]];
        } catch (\Throwable $ex) {
            Db::rollBack();
            throw new BusinessException('创建群组失败' . $ex->getMessage());
        }
    }

    /**
     * 群成员编辑
     * @param array $params
     * @return array
     * @throws BusinessException
     */
    public function groupMemberEditService(array $params)
    {
        //删除群成员
        if ($params['type'] === 'delete') {
            // 删除群成员表数据，删除群成员操作记录数据
            $groupMember = GroupMember::where(['group_number' => $params['group_number'], 'uid' => $params['uid']])->first();
            if ($groupMember) {
                $groupMember->delete();
            }
            return ['code' => 200, 'msg' => '删除成功'];
        }
        //角色编辑
        if ($params['type'] === 'rolesEdit') {
            $role = ['leader', 'admin', 'member'];
            if (!isset($params['role']) || empty($params['role'])) {
                return ['code' => 500, 'msg' => 'type:rolesEdit 的时候必须传 role'];
            }
            if (!array_search($params['role'], $role)) {
                throw new BusinessException('暂不支持改类型');
            }
            //如果role=leader,则转让群，修改原群主role,修改新群主role
            if ($params['role'] === 'leader') {
                Db::beginTransaction();
                try {
                    //修改新群主角色
                    $groupMembers = GroupMember::where(['group_number' => $params['group_number'], 'uid' => $params['uid']])->first();
                    if ($groupMembers) {
                        $groupMembers->type = 'leader';
                        $groupMembers->save();
                    }
                    //修改原群主角色
                    $leader = GroupMember::where(['group_number' => $params['group_number'], 'uid' => Context::get('uid')])->first();
                    if ($leader) {
                        $groupMembers->type = 'member';
                        $groupMembers->save();
                    }
                    Db::commit();
                    return ['code' => 200, 'msg' => '转让成功'];
                } catch (\Throwable $ex) {
                    Db::rollBack();
                    throw new BusinessException($ex->getMessage());
                }
            }
            //将成员变成群管
            if ($params['role'] === 'admin') {
                $groupMembers = GroupMember::where(['group_number' => $params['group_number'], 'uid' => $params['uid']])->first();
                if ($groupMembers) {
                    $groupMembers->type = 'admin';
                    $groupMembers->save();
                }
                return ['code' => 200, 'msg' => '群管设置成功'];
            }
        }
    }

    /**
     * 解散群组
     * @param string $groupNumber
     * @return array
     * @throws BusinessException
     */
    public function dissolutionService(string $groupNumber)
    {
        $group = Group::find($groupNumber);
        if (!$group) {
            throw new BusinessException('群组不存在');
        }

        Db::beginTransaction();
        try {

            //删除群组，删除群组成员，删除群组相关的记录
            $group->delete();

            GroupMember::query()->where('group_number', $groupNumber)->delete();

            Db::commit();

            return ['code' => 200, 'msg' => '解散群组成功'];
        } catch (\Throwable $ex) {
            Db::rollBack();
            throw new BusinessException('解散群组失败' . $ex->getMessage());
        }
    }

    /**
     * 获取群组详情
     * @param string $groupNumber
     * @return array
     */
    public function getGroupDetail(string $groupNumber)
    {
        $group = Group::find($groupNumber);

        $data = [];
        if ($group) {

            //查询群组成员
            $member = [];
            $groupMembers = GroupMember::where('group_number', $groupNumber)->get();
            foreach ($groupMembers as $item) {
                $members = Member::find($item->uid);
                if ($members) {
                    $member[] = [
                        'uid' => $members->uid,
                        'username' => $members->username,
                        'email' => $members->email,
                        'head_image' => picturePath($members->head_image),
                        'nickname' => $members->nickname,
                        'autograph' => $members->autograph,
                        'created_at' => $item->created_at,
                        'type' => $item->type,
                    ];
                }
            }

            $data = [
                'detail' => $group,
                'members' => $member,
            ];
        }

        return $data;
    }

    /**
     * 移除群
     * @param string $groupNumber
     * @param string $uid
     * @return false|int|mixed
     */
    public function deleteGroupMember(string $groupNumber, string $uid)
    {
        return GroupMember::where('group_number', $groupNumber)->where('uid', $uid)->delete();
    }
}