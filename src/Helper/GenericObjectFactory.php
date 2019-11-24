<?php

declare(strict_types=1);

namespace MonologFactory\Helper;

use Cascader\Cascader;

final class GenericObjectFactory implements ObjectFactory
{
    /** @var Cascader */
    private $cascader;

    public function __construct(Cascader $cascader)
    {
        $this->cascader = $cascader;
    }

    public function create(string $className, array $parameters = []): object
    {
        return $this->cascader->create($className, $parameters);
    }
}
