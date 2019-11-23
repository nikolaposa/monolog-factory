<?php

declare(strict_types=1);

namespace MonologFactory\Exception;

use Psr\Container\ContainerInterface;
use RuntimeException;

final class BadStaticDiContainerFactoryUsage extends RuntimeException implements MonologFactoryException
{
    public static function missingContainerArgument(string $factoryClassName): self
    {
        return new self(sprintf(
            'The first argument for %s must be %s implementation',
            $factoryClassName,
            ContainerInterface::class
        ));
    }
}
