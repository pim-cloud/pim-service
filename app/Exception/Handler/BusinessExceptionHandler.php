<?php
declare(strict_types=1);

namespace App\Exception\Handler;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class BusinessExceptionHandler extends ExceptionHandler
{
    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    public function __construct(StdoutLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        return $response
            ->withHeader('Message-Type','application/json')
            ->withHeader('Server', 'im')
            ->withStatus($throwable->getCode())
            ->withBody(new SwooleStream(json_encode(['code' => $throwable->getCode(), 'msg' => $throwable->getMessage()])));
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}