<?php

declare(strict_types=1);

namespace MonologFactory\Tests\TestAsset\Logger;

use RuntimeException;

class ErroneousHandlerFactory
{
    public function __construct()
    {
        throw new RuntimeException('Intentional error');
    }
}
