<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\MessageService;
use App\Request\MsgRecordRequest;
use App\Request\SendMessageRequest;
use App\Exception\ValidateException;
use App\Middleware\Auth\AuthMiddleware;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;

/**
 * @Controller(prefix="message")
 * @Middleware(AuthMiddleware::class)
 */
class MessageController extends AbstractController
{
    /**
     * 发送消息
     * @PostMapping(path="sendMessage")
     */
    public function sendMessage(SendMessageRequest $request)
    {
        return $this->apiReturn((new MessageService())->sendMessageService($request->post()));
    }

    /**
     * 消息确认
     * @GetMapping(path="ack")
     */
    public function ack()
    {
        $msgId = $this->request->query('msgId');
        if (!$msgId) {
            throw new ValidateException('msgId 必须');
        }
        return $this->apiReturn((new MessageService())->ackService($msgId));
    }

    /**
     * 历史消息
     * @GetMapping(path="getMsgRecord")
     */
    public function getMsgRecord(MsgRecordRequest $request)
    {
        return $this->apiReturn((new MessageService())->getMsgRecordService($request->query()));
    }
}