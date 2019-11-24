<?php

declare(strict_types=1);

namespace MonologFactory\Config;

class ParametrizedObjectConfig extends BaseConfig
{
    public const NAME = 'name';
    public const PARAMETERS = 'params';

    /** @var string */
    protected $name;

    /** @var array */
    protected $parameters;

    protected function __construct(string $name, array $parameters)
    {
        $this->name = $name;
        $this->parameters = $parameters;
    }

    public static function fromArray(array $config)
    {
        $config = self::filter($config);

        return new static($config[self::NAME], $config[self::PARAMETERS]);
    }

    protected static function defaults(): array
    {
        return [
            self::NAME => '',
            self::PARAMETERS => [],
        ];
    }

    protected static function validate(array $config): void
    {
        ConfigAssertion::notEmptyString($config[self::NAME], "'" . self::NAME . "' is required and cannot be empty");
        ConfigAssertion::isArray($config[self::PARAMETERS], "'" . self::PARAMETERS . "' must be an array");
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }
}
