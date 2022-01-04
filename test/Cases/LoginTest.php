<?php

namespace Cases;

use HyperfTest\HttpTestCase;

class LoginTest extends HttpTestCase
{
    public function testlogin()
    {
        $res = $this->client->post('/login/login', ['username' => 'jksusu', 'password' => 'jksusu']);
        $this->assertSame(200, $res['code'], 'code <> 200');
        $this->assertSame('ea90ffa4ddf48142fd5af34b1cc2062d', $res['data']['token'], 'token <> ea90ffa4ddf48142fd5af34b1cc2062d');
    }
}