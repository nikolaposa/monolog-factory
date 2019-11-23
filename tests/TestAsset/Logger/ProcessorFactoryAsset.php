<?php

declare(strict_types=1);

namespace MonologFactory\Tests\TestAsset\Logger;

use Monolog\Processor\MemoryUsageProcessor;
use Psr\Container\ContainerInterface;

class ProcessorFactoryAsset
{
    public function __invoke(ContainerInterface $container)
    {
        return new MemoryUsageProcessor();
    }
}
