<?php

declare(strict_types=1);

namespace MonologFactory\Exception;

use RuntimeException;
use Throwable;

final class CannotResolveLoggerComponent extends RuntimeException implements MonologFactoryException
{
    public static function unknownService(string $serviceOrFactory): self
    {
        return new self(sprintf("Cannot resolve '%s' logger component to a service or a factory class", $serviceOrFactory));
    }

    public static function resolutionFailed(string $serviceOrFactory, Throwable $previous): self
    {
        return new self(
            sprintf("Resolution of a '%s' logger component has failed. See previous error for more details.", $serviceOrFactory),
            0,
            $previous
        );
    }
}
