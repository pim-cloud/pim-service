<?php

declare(strict_types=1);

namespace App\Service;

use App\Job\EmailCheckCodeJob;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\Driver\DriverInterface;

class QueueService
{
    /**
     * @var DriverInterface
     */
    protected $driver;

    public function __construct(DriverFactory $driverFactory)
    {
        $this->driver = $driverFactory->get('default');
    }

    /**
     * 发送邮件验证码
     * @param $params
     * @param int $delay
     * @return bool
     */
    public function sendCheckCode($params, int $delay = 0): bool
    {
        return $this->driver->push(new EmailCheckCodeJob($params), $delay);
    }
}