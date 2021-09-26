<?php
declare(strict_types=1);

namespace App\Exception;

use Throwable;
use Hyperf\Server\Exception\ServerException;

class AuthException extends ServerException
{
    public function __construct(string $message = null, int $code = 401, Throwable $previous = null)
    {
        $msg = '授权码过期:'.$message;

        parent::__construct($msg, $code, $previous);
    }
}