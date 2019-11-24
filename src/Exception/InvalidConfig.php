<?php

declare(strict_types=1);

namespace MonologFactory\Exception;

use Assert\InvalidArgumentException;

final class InvalidConfig extends InvalidArgumentException implements MonologFactoryException
{
}
