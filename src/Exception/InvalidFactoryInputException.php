<?php

declare(strict_types=1);

namespace MonologFactory\Exception;

class InvalidFactoryInputException extends InvalidArgumentException
{
    public static function forMissingName()
    {
        return new self("'name' is missing from the factory input");
    }
    
    public static function forInvalidOptions($options)
    {
        return new self(sprintf("Factory input 'options' should be an array; %s given", gettype($options)));
    }
}
