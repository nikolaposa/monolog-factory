<?php

declare(strict_types=1);

namespace MonologFactory\Exception;

use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\HandlerInterface;

class InvalidOptionsException extends InvalidArgumentException
{
    public static function forInvalidHandlers($handlers)
    {
        return new self(sprintf("'handlers' should be an array; %s given", gettype($handlers)));
    }

    public static function forInvalidProcessors($processors)
    {
        return new self(sprintf("'processors' should be an array; %s given", gettype($processors)));
    }

    public static function forInvalidHandler($handler)
    {
        return new self(sprintf(
            "'handlers' item should be either %s instance or an factory input array; %s given",
            HandlerInterface::class,
            gettype($handler)
        ));
    }

    public static function forInvalidProcessor($processor)
    {
        return new self(sprintf(
            "'processors' item should be either callable or an factory input array; %s given",
            gettype($processor)
        ));
    }

    public static function forInvalidFormatter($formatter)
    {
        return new self(sprintf(
            "Handler 'formatter' should be either %s instance or an factory input array; %s given",
            FormatterInterface::class,
            gettype($formatter)
        ));
    }
}
