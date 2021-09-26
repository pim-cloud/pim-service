<?php


namespace Cases;


use HyperfTest\HttpTestCase;

class ContactsTest extends HttpTestCase
{

    protected $username = 'ppx';

    public function testsearchUsers()
    {
        //搜索好友，发送好友申请
        $res = $this->client->get('/contacts/searchUsers',
            ['keyword' => $this->username, 'currentPage' => 10, 'perPage' => 1],
            ['authentication' => $this->token,]
        );

        $this->assertSame($res['code'], 200);

        //列表用户uid
        $uid = $res['data']['data'][0]['uid'];
        $add = $this->client->post(
            '/contacts/sendAddFriendRequest',
            ['uid' => $uid, 'remarks' => 'test'],
            ['authentication' => $this->token,]
        );
        $this->assertSame($add['code'], 200);
    }

    public function testgetFriendsRequestList()
    {
        $res = $this->client->get(
            '/contacts/getFriendsRequestList',
            [],
            ['authentication' => $this->token,]
        );
        $this->assertSame($res['code'], 200);
    }

    public function testgetContactsList()
    {
        $res = $this->client->get('/contacts/getContactsList',
            [],
            ['authentication' => $this->token,]
        );
        $this->assertSame($res['code'], 200);
    }
}