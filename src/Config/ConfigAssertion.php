<?php

declare(strict_types=1);

namespace MonologFactory\Config;

use Assert\Assertion;
use MonologFactory\Exception\InvalidConfig;

/**
 * @method static bool allIsArrayOrInstanceOf(mixed[] $value, string $className, $message = null)
 * @method static bool allIsArrayOrCallable(mixed[] $value, string $className, $message = null)
 * @method static bool nullOrIsArrayOrInstanceOf(mixed[] $value, string $className, $message = null)
 * @method static bool nullOrIsArrayOrCallable(mixed[] $value, $message = null)
 */
final class ConfigAssertion extends Assertion
{
    protected static $exceptionClass = InvalidConfig::class;

    public static function notEmptyString($value, $message = null): bool
    {
        self::minLength($value, 1, $message);

        return true;
    }

    public static function isArrayOrInstanceOf($value, string $className, $message = null): bool
    {
        if (!is_array($value)) {
            static::isInstanceOf($value, $className, $message);
        }

        return true;
    }

    public static function isArrayOrCallable($value, $message = null): bool
    {
        if (!is_array($value)) {
            static::isCallable($value, $message);
        }

        return true;
    }
}
