<?php

declare(strict_types=1);

namespace MonologFactory;

use MonologFactory\Exception\InvalidFactoryInput;

final class FactoryInput
{
    /** @var string */
    private $name;

    /** @var array */
    private $options;
    
    private function __construct(string $name, array $options)
    {
        $this->name = $name;
        $this->options = $options;
    }

    public static function fromArray(array $input)
    {
        if (! array_key_exists('name', $input)) {
            throw InvalidFactoryInput::missingName();
        }
        
        $name = $input['name'];
        $options = $input['options'] ?? [];
        
        if (! is_array($options)) {
            throw InvalidFactoryInput::invalidOptions($options);
        }
        
        return new self($name, $options);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
