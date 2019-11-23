<?php

declare(strict_types=1);

namespace MonologFactory\Exception;

use InvalidArgumentException;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\HandlerInterface;

final class InvalidOptions extends InvalidArgumentException implements MonologFactoryException
{
    public static function invalidHandlers($handlers): self
    {
        return new self(sprintf("'handlers' should be an array; %s given", gettype($handlers)));
    }

    public static function invalidHandler($handler): self
    {
        return new self(sprintf(
            "'handlers' item should be either %s instance or an factory input array; %s given",
            HandlerInterface::class,
            gettype($handler)
        ));
    }

    public static function invalidProcessors($processors): self
    {
        return new self(sprintf("'processors' should be an array; %s given", gettype($processors)));
    }

    public static function invalidProcessor($processor): self
    {
        return new self(sprintf(
            "'processors' item should be either callable or an factory input array; %s given",
            gettype($processor)
        ));
    }

    public static function invalidFormatter($formatter): self
    {
        return new self(sprintf(
            "Handler 'formatter' should be either %s instance or an factory input array; %s given",
            FormatterInterface::class,
            gettype($formatter)
        ));
    }
}
