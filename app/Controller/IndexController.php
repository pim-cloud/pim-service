<?php

namespace App\Controller;

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

    }

}