<?php

declare(strict_types=1);

namespace MonologFactory;

use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use MonologFactory\Exception\InvalidArgumentException;
use MonologFactory\Exception\LoggerComponentNotResolvedException;
use Psr\Container\ContainerInterface;
use Throwable;

abstract class AbstractDiContainerLoggerFactory
{
    /** @var string */
    protected $loggerName;

    /** @var ContainerInterface */
    private $container;

    /** @var LoggerFactory */
    private $loggerFactory;

    public function __construct(string $loggerName = 'default')
    {
        $this->loggerName = $loggerName;
    }

    public function __invoke(ContainerInterface $container): Logger
    {
        $this->container = $container;

        $loggerConfig = array_merge([
            'name' => $this->loggerName,
            'handlers' => [],
            'processors' => [],
        ], $this->getLoggerConfig($this->loggerName));

        return $this->createLogger($loggerConfig);
    }

    public static function __callStatic(string $name, array $arguments): Logger
    {
        if (0 === count($arguments) || ! ($container = current($arguments)) instanceof ContainerInterface) {
            throw new InvalidArgumentException(sprintf(
                'The first argument for %s must be of type %s',
                static::class,
                ContainerInterface::class
            ));
        }

        return (new static($name))->__invoke($container);
    }

    abstract protected function getLoggerConfig(string $loggerName): array;

    protected function createLogger(array $config): Logger
    {
        $name = $config['name'];
        unset($config['name']);

        try {
            if (is_array($config['handlers'])) {
                $config['handlers'] = $this->prepareHandlers($config['handlers']);
            }

            if (is_array($config['processors'])) {
                $config['processors'] = $this->prepareProcessors($config['processors']);
            }
        } catch (Throwable $ex) {
            throw LoggerComponentNotResolvedException::fromError($ex);
        }

        return $this->getLoggerFactory()->createLogger($name, $config);
    }

    protected function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    private function prepareHandlers(array $handlers): array
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

    private function prepareProcessors(array $processors): array
    {
        return array_map(function ($processor) {
            if (is_string($processor)) {
                return $this->resolveProcessor($processor);
            }

            return $processor;
        }, $processors);
    }

    private function resolveHandler(string $handlerName): HandlerInterface
    {
        return $this->resolveFromContainer($handlerName);
    }

    private function resolveFormatter(string $formatterName): FormatterInterface
    {
        return $this->resolveFromContainer($formatterName);
    }

    private function resolveProcessor(string $processorName): callable
    {
        return $this->resolveFromContainer($processorName);
    }

    private function resolveFromContainer(string $serviceOrFactory)
    {
        if ($this->container->has($serviceOrFactory)) {
            return $this->container->get($serviceOrFactory);
        }

        if (class_exists($serviceOrFactory)) {
            $factory = new $serviceOrFactory();
            return $factory($this->container);
        }

        return null;
    }

    private function getLoggerFactory(): LoggerFactory
    {
        if (null === $this->loggerFactory) {
            $this->loggerFactory = new LoggerFactory();
        }

        return $this->loggerFactory;
    }
}
