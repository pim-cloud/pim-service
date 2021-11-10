<?php

declare(strict_types=1);

namespace App\Event;

class SendMailEvent
{
    public $member;

    public function __construct($member)
    {
        $this->member = $member;
    }
}