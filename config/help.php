<?php

/**
 * 获取当前系统唯一标识
 */
if (!function_exists('getLocalUnique')) {
    function getLocalUnique(): string
    {
        return 'test';
        return substr(md5(implode(',', swoole_get_local_ip())), 0, 10);
    }
}

/**
 * 获取雪花ID
 */
if (!function_exists('getSnowflakeId')) {
    function getSnowflakeId(): string
    {
        $container = \Hyperf\Utils\ApplicationContext::getContainer();
        $generator = $container->get(\Hyperf\Snowflake\IdGeneratorInterface::class);
        return $generator->generate();
    }
}

/**
 * 投递消息
 */
if (!function_exists('enter')) {
    function enter($params): string
    {
        $stringId = Hyperf\Utils\ApplicationContext::getContainer()
            ->get(\App\Stream\Producer\Producer::class)
            ->push(getLocalUnique(), $params);
        $id = '';
        if ($stringId) {
            $id = str_replace('-', '', $stringId);
        }
        return $id;
    }
}

/**
 * 确认消息
 */
if (!function_exists('ack')) {
    function ack(string $msgIds)
    {
        return Hyperf\Utils\ApplicationContext::getContainer()
            ->get(\App\Stream\Consumer\Consumer::class)
            ->ack(getLocalUnique(), getLocalUnique(), [$msgIds]);
    }
}

/**
 * 获取用户信息
 */
if (!function_exists('getMmeberInfo')) {
    function getMmeberInfo(string $uid)
    {
        return Hyperf\Utils\ApplicationContext::getContainer()
            ->get(\App\Tools\RedisTools::class)
            ->getMemberInfo($uid);
    }
}
