<?php

namespace Cases;

use HyperfTest\HttpTestCase;

class GroupTest extends HttpTestCase
{

    public function testcreate()
    {
        //创建群
        $res = $this->client->post('/group/create',
            [
                'group_name' => 'test',
                'group_head_image' => 'test',
                'introduction' => 'testtesttest'
            ],
            ['authentication' => $this->token],
        );

        $this->assertSame($res['code'], 200);
        $groupNumber = $res['data']['groupNumber'];

        //解散群
        $res1 = $this->client->post('/group/dissolution',
            ['group_number'=>$groupNumber],
            ['authentication' => $this->token],
        );
        $this->assertSame($res1['code'], 200);
    }

}