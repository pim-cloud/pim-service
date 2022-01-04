<?php

function container()
{
    return \Hyperf\Utils\ApplicationContext::getContainer();
}

/**
 * 获取当前系统唯一标识
 */
function getLocalUnique(): string
{
    return substr(md5(implode(',', swoole_get_local_ip())), 0, 10);
}

/**
 * 获取雪花ID
 */
function getSnowflakeId(): string
{
    $generator = container()->get(\Hyperf\Snowflake\IdGeneratorInterface::class);
    return $generator->generate();
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
function ack(string $msgIds)
{
    return \App\Redis\MessageQueue::getInstance()
        ->acks('queue:' . getLocalUnique(), 'test', $msgIds);
}

/**
 * 私钥解密数据
 */
function decrypt($ciphertext)
{
    if (!file_exists(env('RSA_PRIVATE_KEY'))) {
        throw new \App\Exception\BusinessException(env('RSA_PRIVATE_KEY') . '私钥不存在');
    }
    openssl_private_decrypt(
        base64_decode($ciphertext),
        $decrypted,
        file_get_contents(env('RSA_PRIVATE_KEY'))
    );
    return $decrypted;
}

/**
 * 获取redis
 */
function redis()
{
    return container()->get(\Hyperf\Redis\Redis::class);
}

/**
 * 测试环境打印
 */
function output($str = '')
{
    if (!env('APP_DEBUG', false)) {
        return '';
    }
    var_dump($str) . PHP_EOL;
}

/**
 * 获取图片链接
 */
function picturePath($path = '')
{
    return empty($path) ? '' : config('file.storage.qiniu.domain') . $path;
}


/**
 * 获取异步队列
 */
function asyncQueue()
{
    return container()->get(\App\Service\QueueService::class);
}

/**
 * 生成code
 * @param int $length
 * @return int
 */
function generateCode($length = 4)
{
    return rand(1000, 9999);
}

