<?php

declare(strict_types=1);

namespace MonologFactory\Tests\TestAsset\Logger;

use Monolog\Handler\NullHandler;
use Psr\Container\ContainerInterface;

class HandlerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new NullHandler();
    }
}
