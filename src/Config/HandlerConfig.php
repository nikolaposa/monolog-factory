<?php

declare(strict_types=1);

namespace MonologFactory\Config;

use Monolog\Formatter\FormatterInterface;

class HandlerConfig extends ParametrizedObjectConfig
{
    public const PROCESSORS = 'processors';
    public const FORMATTER = 'formatter';

    /** @var callable[]|ProcessorConfig[] */
    protected $processors;

    /** @var FormatterInterface|FormatterConfig|null */
    protected $formatter;

    protected function __construct(
        string $name,
        array $parameters,
        array $processors,
        $formatter = null
    ) {
        parent::__construct($name, $parameters);
        $this->processors = $processors;
        $this->formatter = $formatter;
    }

    public static function fromArray(array $config)
    {
        $config = self::filter($config);

        return new static(
            $config[self::NAME],
            $config[self::PARAMETERS],
            array_map(function ($processor) {
                return is_array($processor) ? ProcessorConfig::fromArray($processor) : $processor;
            }, $config[self::PROCESSORS]),
            is_array($config[self::FORMATTER])
                ? FormatterConfig::fromArray($config[self::FORMATTER])
                : $config[self::FORMATTER]
        );
    }

    protected static function defaults(): array
    {
        return array_merge(parent::defaults(), [
            self::PROCESSORS => [],
            self::FORMATTER => null,
        ]);
    }

    protected static function validate(array $config): void
    {
        parent::validate($config);
        ConfigAssertion::isArray($config[self::PROCESSORS], "'" . self::PROCESSORS . "' must be an array");
        ConfigAssertion::allIsArrayOrCallable($config[self::PROCESSORS], "'" . self::PROCESSORS . "' must be an array of callables or configuration arrays");
        ConfigAssertion::nullOrIsArrayOrInstanceOf($config[self::FORMATTER], FormatterInterface::class, "'" . self::FORMATTER . "' must be Formatter instance or configuration array");
    }

    public function getProcessors(): array
    {
        return $this->processors;
    }

    public function getFormatter()
    {
        return $this->formatter;
    }
}
