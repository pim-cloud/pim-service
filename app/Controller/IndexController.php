<?php

namespace App\Controller;

use App\Model\ContactsFriend;
use App\Model\Member;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;

/**
 * @Controller(prefix="index")
 */
class IndexController extends AbstractController
{
    /**
     * @GetMapping(path="index")
     */
    public function index()
    {
        $member = Member::where('code', '!=', '324967106233786369')->get();

        foreach ($member as $key => $val) {
            $time = date('Y-m-d m:i:s');
            ContactsFriend::create([
                'main_code' => '324967106233786369',
                'accept_code' => $val->code,
                'created_at' => $time,
                'updated_at' => $time,
            ]);
            ContactsFriend::create([
                'main_code' => $val->code,
                'accept_code' => '324967106233786369',
                'created_at' => $time,
                'updated_at' => $time,
            ]);
        }

//        $el = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
//        $a = 200;
//        $b = 0;
//        while ($b < $a) {
//            $b++;
//            $name = $el[rand(1, 24)];
//            Member::create([
//                'code' => uniqid(),
//                'username' => $name,
//                'email' => uniqid(),
//                'password' => uniqid(),
//                'salt' => uniqid(),
//                'head_image' => 'morentouxiang.png',
//                'nickname' => $name,
//                'created_at' => date('Y-m-d m:i:s'),
//                'updated_at' => date('Y-m-d m:i:s'),
//            ]);
//        }
    }
}