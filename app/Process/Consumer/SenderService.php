<?php

declare(strict_types=1);

namespace App\Process\Consumer;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\WebSocketServer\Sender;

/**
 * @AutoController()
 */
class SenderService
{
    /**
     * @Inject()
     * @var Sender
     */
    protected $sender;

    public function close(int $fd)
    {
        go(function () use ($fd) {
            sleep(1);
            $this->sender->disconnect($fd);
        });
        return '';
    }

    public function send(int $fd, $data)
    {
        return $this->sender->push($fd, $data);
    }
}