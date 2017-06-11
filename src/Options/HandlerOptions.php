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
    
    protected static function validate(array $options)
    {
        if (array_key_exists('formatter', $options)) {
            $formatter = $options['formatter'];
            
            if (! ($formatter instanceof FormatterInterface || is_array($formatter))) {
                throw InvalidOptionsException::forInvalidFormatter($formatter);
            }
        }
    }
}
