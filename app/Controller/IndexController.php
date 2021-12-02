<?php

namespace App\Controller;

use App\Support\Mail\Email;
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
        Email::send('jksusuppx@qq.com');
    }
}