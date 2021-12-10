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
        $code = time();

        Db::beginTransaction();
        try {
            Group::create([
                'code' => $code,
                'nickname' => $params['nickname'],
                'head_image' => $params['head_image'],
                'introduction' => isset($params['introduction']) && !empty($params['introduction']) ? $params['introduction'] : '这是一个有趣的群',
            ]);
            //添加群主
            GroupMember::create([
                'code' => $code,
                'm_code' => Context::get('code'),
                'type' => 'leader',
            ]);
            foreach ($params['group_member'] as $k => $v) {
                GroupMember::create([
                    'code' => $code,
                    'm_code' => $v,
                    'type' => 'member',
                ]);
            }
            Db::commit();
            //推送创建群聊消息
            enter([
                'send_code' => Context::get('code'),
                'accept_code' => $code,
                'message_type' => 'create_group',
                'head_image' => $params['head_image'],
                'nickname' => $params['nickname'],
            ]);


            return ['code' => 200, 'msg' => '创建成功', 'data' => ['code' => $code]];
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
            $groupMember = GroupMember::where(['code' => $params['code'], 'm_code' => $params['code']])->first();
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
                    $groupMembers = GroupMember::where(['code' => $params['code'], 'm_code' => $params['m_code']])->first();
                    if ($groupMembers) {
                        $groupMembers->type = 'leader';
                        $groupMembers->save();
                    }
                    //修改原群主角色
                    $leader = GroupMember::where(['code' => $params['code'], 'm_code' => Context::get('code')])->first();
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
                $groupMembers = GroupMember::where(['code' => $params['code'], 'm_code' => $params['code']])->first();
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
     * @param string $code
     * @return array
     */
    public function getGroupDetail(string $code)
    {
        $group = Group::find($code);

        $data = [];
        if ($group) {

            //查询群组成员
            $member = [];
            $groupMembers = GroupMember::where('code', $code)->get();
            foreach ($groupMembers as $item) {
                $members = Member::find($item->m_code);
                if ($members) {
                    $member[] = [
                        'code' => $members->code,
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
     * @param string $code
     * @param string $m_code
     * @return false|int|mixed
     */
    public function deleteGroupMember(string $code, string $m_code)
    {
        return GroupMember::where('code', $code)->where('m_code', $m_code)->delete();
    }
}