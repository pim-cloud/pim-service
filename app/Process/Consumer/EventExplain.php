<?php

declare(strict_types=1);

namespace App\Process\Consumer;

use App\Redis\OnLine;
use App\Model\GroupMember;

class EventExplain
{
    protected $sender;

    public function __construct()
    {
        $this->sender = new SenderService();
    }


    public function consume($data)
    {
        if (empty($data)) {
            return false;
        }
        output('开始分发消息**' . $data['message_type']);
        switch ($data['message_type']) {
            case 'chat':
                output($data['accept_type']);
                if ($data['accept_type'] === 'group') {
                    $this->groupRadioBroadcast($data);
                } else {
                    $this->singleSend($data);
                }
                break;
            case 'create_group':
                output('创建群聊，给群成员广播消息');
                $this->groupRadioBroadcast($data);
                break;
            default:
                //未匹配到的类型,默认单条发送
                $this->singleSend($data);
                break;
        };
    }

    /**
     * 群广播消息
     * @param $data
     */
    public function groupRadioBroadcast($data)
    {
        var_dump($data);
        $members = GroupMember::where('code', $data['accept_code'])->get();

        foreach ($members as $item) {
            //不给发送人推送消息
            if ($item->m_code === $data['main_code']) {
                continue;
            }
            //获取web端连接标识
            $fd = OnLine::getInstance()->getFdByUid('web:', $item->m_code);
            $this->send($fd, $data);
        }
    }

    /**
     * 发送单条消息
     * @param $data
     */
    public function singleSend($data)
    {
        $fd = OnLine::getInstance()->getFdByUid('web:', $data['accept_code']);
        $this->send($fd, $data);
    }

    public function send($fd, $data)
    {
        output('推送消息**' . $data['main_code'] . '*接收人*' . $data['accept_code'] . '***FD***' . $fd);
        $this->sender->send($fd, json_encode($data));
    }
}