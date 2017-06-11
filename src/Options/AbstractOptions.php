<?php

declare(strict_types=1);

namespace MonologFactory\Options;

abstract class AbstractOptions
{
    /**
     * @var array
     */
    protected $options;
    
    protected function __construct(array $options)
    {
        $this->options = $options;
    }
    
    public static function fromArray(array $options)
    {
        $options = static::normalize($options);
        static::validate($options);

        return new static($options);
    }

    public function toArray() : array
    {
        return $this->options;
    }

    protected static function normalize(array $options) : array
    {
        return $options;
    }

    protected static function validate(array $options)
    {
    }

    final protected function get(string $key, $default = null)
    {
        return array_key_exists($key, $this->options)
            ? $this->options[$key]
            : $default;
    }
}
