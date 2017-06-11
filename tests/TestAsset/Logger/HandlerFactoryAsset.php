<?php

declare(strict_types=1);

namespace MonologFactory\Tests\TestAsset\Logger;

use Interop\Container\ContainerInterface;
use Monolog\Handler\NullHandler;

class HandlerFactoryAsset
{
    public function __invoke(ContainerInterface $container)
    {
        return new NullHandler();
    }
}
