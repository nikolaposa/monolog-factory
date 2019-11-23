<?php

declare(strict_types=1);

namespace MonologFactory\Options;

use Monolog\Handler\HandlerInterface;
use MonologFactory\Exception\InvalidOptions;

final class LoggerOptions extends AbstractOptions
{
    public function getHandlers(): array
    {
        return $this->get('handlers', []);
    }

    public function getProcessors(): array
    {
        return $this->get('processors', []);
    }

    protected static function validate(array $options)
    {
        if (array_key_exists('handlers', $options)) {
            $handlers = $options['handlers'];

            if (! is_array($handlers)) {
                throw InvalidOptions::invalidHandlers($handlers);
            }

            foreach ($handlers as $handler) {
                if (! ($handler instanceof HandlerInterface || is_array($handler))) {
                    throw InvalidOptions::invalidHandler($handler);
                }
            }
        }

        if (array_key_exists('processors', $options)) {
            $processors = $options['processors'];

            if (! is_array($processors)) {
                throw InvalidOptions::invalidProcessors($options['processors']);
            }

            foreach ($processors as $processor) {
                if (! (is_callable($processor) || is_array($processor))) {
                    throw InvalidOptions::invalidProcessor($processor);
                }
            }
        }
    }
}
