<?php
declare(strict_types=1);

namespace App\Exception;

use Throwable;
use Hyperf\Server\Exception\ServerException;

class ValidateException extends ServerException
{
    public function __construct(string $message = null, int $code = 500, Throwable $previous = null)
    {
        $msg = '参数验证失败:' . $message;

        parent::__construct($msg, $code, $previous);
    }
}