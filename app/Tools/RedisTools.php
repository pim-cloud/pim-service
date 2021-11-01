<?php

declare(strict_types=1);

namespace App\Tools;


class RedisTools
{
    public $redis;

    public function __construct()
    {
        $this->redis = \Hyperf\Utils\ApplicationContext::getContainer()->get(\Hyperf\Redis\Redis::class);
    }

    /**
     * 设置token缓存
     * @param string $token 用户token
     * @param string $uid 用户uid
     * @param int $timeOut 缓存失效时间
     * @return bool
     */
    public function setTokenMappingUid(string $token, string $uid, $timeOut = 10000000): bool
    {
        return $this->redis->set($token, $uid, $timeOut);
    }

    /**
     * uid映射用户数据
     * @param string $uid
     * @param array $data
     * @return bool
     */
    public function setUidMappingMember(string $uid, array $data): bool
    {
        return $this->redis->hMSet('memberInfo>>' . $uid, $data);
    }

    /*
     * 获取h5在线fd
     */
    public function getH5OnLineFd($uid)
    {
        return $this->redis->hGet('uidOnLine>>' . $uid, 'h5_fd');
    }

    public function delH5OnLineFd($fd)
    {
        return $this->redis->del(getLocalUnique() . 'devicesFd>>' . $fd);
    }

    /**
     * 获取 memberInfo
     * @param  $uid
     * @return array
     */
    public function getMemberInfo($uid): array
    {
        $info = $this->redis->hGetAll('memberInfo>>' . $uid);
        return [
            'username' => $info['username'],
            'head_image' => $info['head_image'],
            'nikename' => $info['nikename'],
            'autograph' => $info['autograph'],
        ];
    }
}