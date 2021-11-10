<?php

/**
 * 获取当前系统唯一标识
 */
if (!function_exists('getLocalUnique')) {
    function getLocalUnique(): string
    {
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
        $stringId = \App\Redis\MessageQueue::getInstance()->push('queue:' . getLocalUnique(), $params);
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
        return \App\Redis\MessageQueue::getInstance()
            ->acks('queue:' . getLocalUnique(), 'test', $msgIds);
    }
}

/**
 * 私钥解密数据
 */
if (!function_exists('decrypt')) {
    function decrypt($ciphertext)
    {
        openssl_private_decrypt(
            base64_decode($ciphertext),
            $decrypted,
            file_get_contents(env('RSA_PRIVATE_KEY'))
        );
        return $decrypted;
    }
}

/**
 * 获取redis
 */
if (!function_exists('redis')) {
    function redis()
    {
        return \Hyperf\Utils\ApplicationContext::getContainer()->get(\Hyperf\Redis\Redis::class);
    }
}

/**
 * 测试环境打印
 */
if (!function_exists('output')) {
    function output($str = '')
    {
        if (!env('APP_DEBUG', false)) {
            return '';
        }
        var_dump($str) . PHP_EOL;
    }
}

/**
 * 获取图片链接
 */
if (!function_exists('picturePath')) {
    function picturePath($path = '')
    {
        return empty($path) ? '' : config('file.storage.qiniu.domain') . $path;
    }
}

/**
 * 获取邮箱实例
 */
if (!function_exists('smail')) {
    function smail()
    {
        $container = \Hyperf\Utils\ApplicationContext::getContainer();
        return $container->get(\App\Support\Mail\Mail::class);
    }
}

