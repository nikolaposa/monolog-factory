<?php

declare(strict_types=1);

namespace MonologFactory\Helper;

interface ObjectFactory
{
    public function create(string $className, array $parameters = []): object;
}
