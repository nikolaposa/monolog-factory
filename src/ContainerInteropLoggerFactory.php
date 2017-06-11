<?php

declare(strict_types=1);

namespace MonologFactory;

use Interop\Container\ContainerInterface;
use Monolog\Logger;
use Monolog\Handler\HandlerInterface;
use Monolog\Formatter\FormatterInterface;

class ContainerInteropLoggerFactory
{
    const CONFIG_KEY = 'logger';

    /**
     * @var string
     */
    protected $name;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var LoggerFactory
     */
    private $loggerFactory;

    public function __construct(string $name = 'default')
    {
        $this->name = $name;
    }

    public function __invoke(ContainerInterface $container) : Logger
    {
        $this->container = $container;

        $loggerConfig = $this->getLoggerConfig($this->name);

        return $this->createLogger($loggerConfig);
    }

    public static function __callStatic($name, $arguments)
    {
        // TODO: Implement __callStatic() method.
    }

    protected function getLoggerConfig(string $loggerName) : array
    {
        $config = $this->container->has('Config') ? $this->container->get('Config') : [];
        $monologConfig = $config[self::CONFIG_KEY] ?? [];
        $loggerConfig = $monologConfig[$loggerName] ?? [];

        return array_merge(
            $this->getDefaultLoggerConfig($loggerName),
            $loggerConfig
        );
    }

    protected function getDefaultLoggerConfig(string $loggerName) : array
    {
        return [
            'name' => $loggerName,
            'handlers' => [],
            'processors' => [],
        ];
    }

    protected function createLogger(array $config) : Logger
    {
        $name = $config['name'];
        unset($config['name']);

        if (is_array($config['handlers'])) {
            $config['handlers'] = $this->marshalHandlers($config['handlers']);
        }

        if (is_array($config['processors'])) {
            $config['processors'] = $this->marshalProcessors($config['processors']);
        }

        return $this->getLoggerFactory()->createLogger($name, $config);
    }

    protected function marshalHandlers(array $handlers) : array
    {
        return array_map(function ($handler) {
            if (is_string($handler)) {
                return $this->resolveHandler($handler);
            }

            if (is_array($handler) && isset($handler['options']['formatter']) && is_string($handler['options']['formatter'])) {
                $handler['options']['formatter'] = $this->resolveFormatter($handler['options']['formatter']);
            }

            return $handler;
        }, $handlers);
    }

    protected function marshalProcessors(array $processors) : array
    {
        return array_map(function ($processor) {
            if (is_string($processor)) {
                return $this->resolveProcessor($processor);
            }

            return $processor;
        }, $processors);
    }

    protected function resolveHandler(string $handler) : HandlerInterface
    {
        return $this->resolveFromContainer($handler, 'handler');
    }

    protected function resolveFormatter($formatter) : FormatterInterface
    {
        return $this->resolveFromContainer($formatter, 'formatter');
    }

    protected function resolveProcessor($processor) : callable
    {
        return $this->resolveFromContainer($processor, 'processor');
    }

    final protected function resolveFromContainer(string $serviceOrFactory, string $type)
    {
        if ($this->container->has($serviceOrFactory)) {
            return $this->container->get($serviceOrFactory);
        }

        if (class_exists($serviceOrFactory)) {
            $factory = new $serviceOrFactory();
            return $factory($this->container);
        }

        throw new \InvalidArgumentException(sprintf('%s %s could not be resolved neither as a service or a factory', $serviceOrFactory, $type));
    }

    final protected function getLoggerFactory() : LoggerFactory
    {
        if (null === $this->loggerFactory) {
            $this->loggerFactory = new LoggerFactory();
        }

        return $this->loggerFactory;
    }
}
