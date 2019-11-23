<?php

declare(strict_types=1);

namespace MonologFactory\Options;

use Monolog\Formatter\FormatterInterface;
use MonologFactory\Exception\InvalidOptionsException;

final class HandlerOptions extends AbstractOptions
{
    public function getFormatter()
    {
        return $this->get('formatter', false);
    }

    public function getProcessors(): array
    {
        return $this->get('processors', []);
    }
    
    protected static function validate(array $options)
    {
        if (array_key_exists('formatter', $options)) {
            $formatter = $options['formatter'];
            
            if (! ($formatter instanceof FormatterInterface || is_array($formatter))) {
                throw InvalidOptionsException::forInvalidFormatter($formatter);
            }
        }

        if (array_key_exists('processors', $options)) {
            $processors = $options['processors'];

            if (! is_array($processors)) {
                throw InvalidOptionsException::forInvalidProcessors($options['processors']);
            }

            foreach ($processors as $processor) {
                if (! (is_callable($processor) || is_array($processor))) {
                    throw InvalidOptionsException::forInvalidProcessor($processor);
                }
            }
        }
    }
}
