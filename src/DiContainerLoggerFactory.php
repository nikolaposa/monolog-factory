<?php

declare(strict_types=1);

namespace MonologFactory;

final class DiContainerLoggerFactory extends AbstractDiContainerLoggerFactory
{
    const CONFIG_KEY = 'logger';

    protected function getLoggerConfig(string $loggerName): array
    {
        $config = [];

        foreach (['config', 'Config'] as $configServiceName) {
            if ($this->getContainer()->has($configServiceName)) {
                $config = $this->getContainer()->get($configServiceName);
                break;
            }
        }
        
        return $config[self::CONFIG_KEY][$loggerName] ?? [];
    }
}
