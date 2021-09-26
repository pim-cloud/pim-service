<?php
declare(strict_types=1);

namespace App\Exception;

use Throwable;
use Hyperf\Server\Exception\ServerException;

/**
 * 联系人模块异常
 * Class BusinessException
 */
class BusinessException extends \Exception
{
    public function __construct(string $message = null, int $code = 202, Throwable $previous = null)
    {
        if (is_null($message)) {
            $message = 'BusinessException';
        }

        parent::__construct($message, $code, $previous);
    }
}