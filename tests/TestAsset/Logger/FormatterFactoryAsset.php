<?php

declare(strict_types=1);

namespace MonologFactory\Tests\TestAsset\Logger;

use Interop\Container\ContainerInterface;
use Monolog\Formatter\HtmlFormatter;

class FormatterFactoryAsset
{
    public function __invoke(ContainerInterface $container)
    {
        return new HtmlFormatter();
    }
}
