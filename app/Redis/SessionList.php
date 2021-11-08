<?php

declare(strict_types=1);

namespace App\Redis;

use App\Redis\Structure\Hash;

class SessionList extends Hash
{
    protected $key = 'redis:session:{uid}';

    public function getKey(string $uid)
    {
        return str_replace('{uid}', $uid, $this->key);
    }

    /**
     * 获取会话列表
     * @param string $uid
     * @return array
     */
    public function sessionLists(string $uid)
    {
        return $this->getAll($this->getKey($uid));
    }

    /**
     * 获取某个字段的值
     * @param string $uid
     * @param  $field
     * @return false|string
     */
    public function getSessionAfield(string $uid, $field)
    {
        return $this->get($this->getKey($uid), $field);
    }

    public function addSessionLists()
    {

    }

    /**
     * 增加一条会话
     * @param string $uid
     * @param  $acceptUid
     * @param $info
     * @return bool
     */
    public function addSession(string $uid, $acceptUid, $info)
    {
        return $this->set($this->getKey($uid), (string)$acceptUid, json_encode($info));
    }

    /**
     * 删除 session
     * @param string $uid
     * @param string $acceptUid
     * @return bool|int
     */
    public function delSession(string $uid, string $acceptUid)
    {
        return $this->del($this->getKey($uid), $acceptUid);
    }

    /**
     * 增加一条未读条数
     * @param $uid
     * @param $acceptUid
     * @return bool
     */
    public function saveSessionUnread($uid, $acceptUid)
    {
        $session = $this->get($this->getKey($uid), $acceptUid);
        if (empty($session)) {
            return false;
        }
        $data = json_decode($session, true);
        $data['unread'] = $data['unread'] + 1;
        return $this->set($this->get($uid), $acceptUid, json_encode($data));
    }
}