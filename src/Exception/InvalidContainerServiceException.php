<?php

declare(strict_types=1);

namespace MonologFactory\Exception;

class InvalidContainerServiceException extends \RuntimeException implements ExceptionInterface
{
    public static function forUnresolved(string $type, string $name)
    {
        return new self(sprintf('%2$s %1$s has not been found in the DI container', $type, $name));
    }

    public static function forInvalid(string $type, string $name, string $validType)
    {
        return new self(sprintf('%2$s %1$s must be of type: %3$s', $type, $name, $validType));
    }
}
