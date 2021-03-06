<?php

declare(strict_types=1);

namespace MonologFactory\Tests\TestAsset\Logger;

use Monolog\Formatter\HtmlFormatter;
use Psr\Container\ContainerInterface;

class FormatterFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new HtmlFormatter();
    }
}
