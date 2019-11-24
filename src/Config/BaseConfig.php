<?php

declare(strict_types=1);

namespace MonologFactory\Config;

use MonologFactory\Exception\InvalidConfig;

abstract class BaseConfig
{
    /**
     * @throws InvalidConfig
     */
    final protected static function filter(array $config): array
    {
        $config = array_merge(static::defaults(), $config);

        static::validate($config);

        return $config;
    }

    protected static function defaults(): array
    {
        return [];
    }

    /**
     * @throws InvalidConfig
     */
    abstract protected static function validate(array $config): void;
}
