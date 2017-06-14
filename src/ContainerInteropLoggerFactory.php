<?php

declare(strict_types=1);

namespace MonologFactory;

use Interop\Container\ContainerInterface;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use MonologFactory\Exception\InvalidArgumentException;
use MonologFactory\Exception\InvalidContainerServiceException;

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

    public static function __callStatic(string $name, array $arguments) : Logger
    {
        if (0 === count($arguments) || ! ($container = current($arguments)) instanceof ContainerInterface) {
            throw new InvalidArgumentException(sprintf(
                'The first argument for %s method must be of type %s',
                __METHOD__,
                ContainerInterface::class
            ));
        }

        return (new static($name))->__invoke($container);
    }

    protected function getLoggerConfig(string $loggerName) : array
    {
        $config = [];
        foreach (['config', 'Config'] as $configServiceName) {
            if ($this->container->has($configServiceName)) {
                $config = $this->container->get($configServiceName);
                break;
            }
        }
        
        $loggerConfig = $config[self::CONFIG_KEY][$loggerName] ?? [];

        return array_merge(
            [
                'name' => $loggerName,
                'handlers' => [],
                'processors' => [],
            ],
            $loggerConfig
        );
    }

    protected function createLogger(array $config) : Logger
    {
        $name = $config['name'];
        unset($config['name']);

        if (is_array($config['handlers'])) {
            $config['handlers'] = $this->prepareHandlers($config['handlers']);
        }

        if (is_array($config['processors'])) {
            $config['processors'] = $this->prepareProcessors($config['processors']);
        }

        return $this->getLoggerFactory()->createLogger($name, $config);
    }

    protected function prepareHandlers(array $handlers) : array
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

    protected function prepareProcessors(array $processors) : array
    {
        return array_map(function ($processor) {
            if (is_string($processor)) {
                return $this->resolveProcessor($processor);
            }

            return $processor;
        }, $processors);
    }

    protected function resolveHandler(string $handlerName) : HandlerInterface
    {
        $handler = $this->resolveFromContainer($handlerName);

        if (null === $handler) {
            throw InvalidContainerServiceException::forUnresolved('handler', $handlerName);
        }

        if (! $handler instanceof HandlerInterface) {
            throw InvalidContainerServiceException::forInvalid('handler', $handlerName, HandlerInterface::class);
        }

        return $handler;
    }

    protected function resolveFormatter(string $formatterName) : FormatterInterface
    {
        $formatter = $this->resolveFromContainer($formatterName);

        if (null === $formatter) {
            throw InvalidContainerServiceException::forUnresolved('formatter', $formatterName);
        }

        if (! $formatter instanceof FormatterInterface) {
            throw InvalidContainerServiceException::forInvalid('formatter', $formatterName, FormatterInterface::class);
        }

        return $formatter;
    }

    protected function resolveProcessor(string $processorName) : callable
    {
        $processor = $this->resolveFromContainer($processorName);

        if (null === $processor) {
            throw InvalidContainerServiceException::forUnresolved('processor', $processorName);
        }

        if (! is_callable($processor)) {
            throw InvalidContainerServiceException::forInvalid('processor', $processorName, 'callable');
        }

        return $processor;
    }

    final protected function resolveFromContainer(string $serviceOrFactory)
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

    final protected function getLoggerFactory() : LoggerFactory
    {
        if (null === $this->loggerFactory) {
            $this->loggerFactory = new LoggerFactory();
        }

        return $this->loggerFactory;
    }
}
