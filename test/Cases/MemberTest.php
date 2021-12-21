<?php

namespace Cases;

use HyperfTest\HttpTestCase;

class MemberTest extends HttpTestCase
{
    public function testgetMemberInfo()
    {
        $res = $this->client->get(
            '/member/getMemberInfo',
            [],
            ['authentication' => $this->token],
        );

        $this->assertSame($res['code'], 200);
    }
}