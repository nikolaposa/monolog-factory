<?php

declare(strict_types=1);

namespace MonologFactory\Exception;

use InvalidArgumentException;

final class InvalidFactoryInput extends InvalidArgumentException implements MonologFactoryException
{
    public static function missingName(): self
    {
        return new self("'name' is missing from the factory input");
    }
    
    public static function invalidOptions($options): self
    {
        return new self(sprintf("'options' should be an array; %s given", gettype($options)));
    }
}
