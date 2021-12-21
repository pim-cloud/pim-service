<?php


namespace Cases;


use HyperfTest\HttpTestCase;

class MessageTest extends HttpTestCase
{
    public function testsend()
    {
        $res = $this->client->post(
            '/message/sendMessage',
            [
                'accept_uid' => '261069149919252481',
                'content' => 'test',
                'content_type' => 'text',
            ],
            ['authentication' => $this->token],
        );

        $this->assertSame($res['code'],200);

        $messageId = $res['data']['msg_id'];

        //消息ack,需要redis ack 暂时没法测试
        /*$res1 = $this->client->get(
            '/message/ack',
            [
                'msgId' => $messageId,
            ],
            ['authentication' => $this->token],
        );
        $this->assertSame($res1['code'],200);*/

        //获取历史消息
        $res2 = $this->client->get(
            '/message/getMsgRecord',
            [
                'last_msg_id' => $messageId,
                'accept_uid' => '261069149919252481',
            ],
            ['authentication' => $this->token],
        );

        $this->assertSame($res2['code'],200);
    }
}