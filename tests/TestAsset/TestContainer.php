<?php

declare(strict_types=1);

namespace MonologFactory\Tests\TestAsset;

use Psr\Container\ContainerInterface;

final class TestContainer implements ContainerInterface
{
    /** @var array */
    private $entries;

    public function __construct(array $entries)
    {
        $this->entries = $entries;
    }

    public function get($id)
    {
        return $this->entries[$id] ?? null;
    }

    public function has($id): bool
    {
        return array_key_exists($id, $this->entries);
    }
}
