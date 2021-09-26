<?php


namespace App\Process\Consumer;

use App\Model\GroupMember;
use App\Model\Member;
use App\Tools\RedisTools;
use Hyperf\Di\Annotation\Inject;

class EventExplain
{

    /**
     * @Inject
     * @var RedisTools
     */
    protected $redisTools;

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
        var_dump('准备分发消息');
        var_dump($data);
        switch ($data['message_type']) {
            case 'chat':
                if ($data['accept_type'] === 'group') {
                    $this->groupRadioBroadcast($data);
                } else {
                    $this->singleSend($data);
                }
                break;
            case 'create_group':
                var_dump('创建群聊,推送群广播消息');
                var_dump($data);
                //群广播
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
        $members = GroupMember::where('group_number', $data['accept_uid'])->get();

        foreach ($members as $value) {
            //不给发送人推送消息
            if ($value->uid === $data['send_uid']) {
                continue;
            }
            $this->sender->send((int)$this->redisTools->getH5OnLineFd($value->uid), json_encode($data));
        }
    }

    /**
     * 发送单条消息
     * @param $data
     */
    public function singleSend($data)
    {
        $this->sender->send((int)$this->redisTools->getH5OnLineFd($data['accept_uid']), json_encode($data));
    }

}