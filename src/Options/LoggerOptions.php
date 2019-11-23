<?php

declare(strict_types=1);

namespace MonologFactory\Options;

use Monolog\Handler\HandlerInterface;
use MonologFactory\Exception\InvalidOptions;

final class LoggerOptions extends AbstractOptions
{
    public const OPTIONS_HANDLERS = 'handlers';
    public const OPTIONS_PROCESSORS = 'processors';

    public function getHandlers(): array
    {
        return $this->get(self::OPTIONS_HANDLERS, []);
    }

    public function getProcessors(): array
    {
        return $this->get(self::OPTIONS_PROCESSORS, []);
    }

    protected static function validate(array $options): void
    {
        if (array_key_exists(self::OPTIONS_HANDLERS, $options)) {
            $handlers = $options[self::OPTIONS_HANDLERS];

            if (! is_array($handlers)) {
                throw InvalidOptions::invalidHandlers($handlers);
            }

            foreach ($handlers as $handler) {
                if (! ($handler instanceof HandlerInterface || is_array($handler))) {
                    throw InvalidOptions::invalidHandler($handler);
                }
            }
        }

        if (array_key_exists(self::OPTIONS_PROCESSORS, $options)) {
            $processors = $options[self::OPTIONS_PROCESSORS];

            if (! is_array($processors)) {
                throw InvalidOptions::invalidProcessors($options[self::OPTIONS_PROCESSORS]);
            }

            foreach ($processors as $processor) {
                if (! (is_callable($processor) || is_array($processor))) {
                    throw InvalidOptions::invalidProcessor($processor);
                }
            }
        }
    }
}
