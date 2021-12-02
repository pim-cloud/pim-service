<?php

declare(strict_types=1);

namespace App\Job;

use App\Support\Mail\Email;
use Hyperf\AsyncQueue\Job;

class EmailCheckCodeJob extends Job
{
    public $params;

    protected $maxAttempts = 3;

    public function __construct($params)
    {
        $this->params = $params;
    }

    public function handle()
    {
        Email::send($this->params['email'], $this->params['body']);
    }
}