<?php

declare(strict_types=1);

namespace MonologFactory\Exception;

class LoggerComponentNotResolvedException extends \RuntimeException implements ExceptionInterface
{
    public static function fromError(\Throwable $error)
    {
        return new self(
            sprintf('Logger component could not be resolved. Reason: %s', $error->getMessage()),
            (int) $error->getCode(),
            $error
        );
    }
}
