<?php

declare(strict_types=1);

namespace MonologFactory\Options;

use Monolog\Formatter\FormatterInterface;
use MonologFactory\Exception\InvalidOptions;

final class HandlerOptions extends AbstractOptions
{
    public const OPTIONS_FORMATTER = 'formatter';
    public const OPTIONS_PROCESSORS = 'processors';

    public function getFormatter()
    {
        return $this->get(self::OPTIONS_FORMATTER, false);
    }

    public function getProcessors(): array
    {
        return $this->get(self::OPTIONS_PROCESSORS, []);
    }
    
    protected static function validate(array $options): void
    {
        if (array_key_exists(self::OPTIONS_FORMATTER, $options)) {
            $formatter = $options[self::OPTIONS_FORMATTER];
            
            if (! ($formatter instanceof FormatterInterface || is_array($formatter))) {
                throw InvalidOptions::invalidFormatter($formatter);
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
