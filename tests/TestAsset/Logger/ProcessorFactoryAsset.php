<?php

declare(strict_types=1);

namespace MonologFactory\Tests\TestAsset\Logger;

use Interop\Container\ContainerInterface;
use Monolog\Processor\MemoryUsageProcessor;

class ProcessorFactoryAsset
{
    public function __invoke(ContainerInterface $container)
    {
        return new MemoryUsageProcessor();
    }
}
