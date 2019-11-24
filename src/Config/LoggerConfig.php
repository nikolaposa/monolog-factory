<?php

declare(strict_types=1);

namespace MonologFactory\Config;

use DateTimeZone;
use Monolog\Handler\HandlerInterface;

class LoggerConfig extends BaseConfig
{
    public const NAME = 'name';
    public const HANDLERS = 'handlers';
    public const PROCESSORS = 'processors';
    public const TIMEZONE = 'timezone';

    /** @var string */
    protected $name;

    /** @var HandlerInterface[]|HandlerConfig[] */
    protected $handlers;

    /** @var callable[]|ProcessorConfig[] */
    protected $processors;

    /** @var DateTimeZone */
    protected $timezone;

    protected function __construct(string $name, array $handlers, array $processors, DateTimeZone $timezone)
    {
        $this->name = $name;
        $this->handlers = $handlers;
        $this->processors = $processors;
        $this->timezone = $timezone;
    }

    public static function fromArray(array $config)
    {
        $config = self::filter($config);

        return new static(
            $config[self::NAME],
            array_map(function ($handler) {
                return is_array($handler) ? HandlerConfig::fromArray($handler) : $handler;
            }, $config[self::HANDLERS]),
            array_map(function ($processor) {
                return is_array($processor) ? ProcessorConfig::fromArray($processor) : $processor;
            }, $config[self::PROCESSORS]),
            new DateTimeZone($config[self::TIMEZONE])
        );
    }

    protected static function defaults(): array
    {
        return [
            self::NAME => '',
            self::HANDLERS => [],
            self::PROCESSORS => [],
            self::TIMEZONE => date_default_timezone_get(),
        ];
    }

    protected static function validate(array $config): void
    {
        ConfigAssertion::notEmptyString($config[self::NAME], "'" . self::NAME . "' is required and cannot be empty");
        ConfigAssertion::isArray($config[self::HANDLERS], "'" . self::HANDLERS . "' must be an array");
        ConfigAssertion::allIsArrayOrInstanceOf($config[self::HANDLERS], HandlerInterface::class, "'" . self::HANDLERS . "' must be an array of Handler instances or configuration arrays");
        ConfigAssertion::isArray($config[self::PROCESSORS], "'" . self::PROCESSORS . "' must be an array");
        ConfigAssertion::allIsArrayOrCallable($config[self::PROCESSORS], "'" . self::PROCESSORS . "' must be an array of callables or configuration arrays");
        ConfigAssertion::string($config[self::TIMEZONE], "'" . self::TIMEZONE . "' must be a string");
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getHandlers(): array
    {
        return $this->handlers;
    }

    public function getProcessors(): array
    {
        return $this->processors;
    }

    public function getTimezone(): DateTimeZone
    {
        return $this->timezone;
    }
}
