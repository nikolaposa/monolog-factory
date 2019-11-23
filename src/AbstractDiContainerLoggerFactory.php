<?php

declare(strict_types=1);

namespace MonologFactory;

use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use MonologFactory\Exception\BadStaticDiContainerFactoryUsage;
use MonologFactory\Exception\CannotResolveLoggerComponent;
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
            throw BadStaticDiContainerFactoryUsage::missingContainerArgument(static::class);
        }

        return (new static($name))->__invoke($container);
    }

    abstract protected function getLoggerConfig(string $loggerName): array;

    protected function createLogger(array $config): Logger
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

    protected function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    private function prepareHandlers(array $handlers): array
    {
        return array_map(function ($handler) {
            if (is_string($handler)) {
                $handler = $this->resolveHandler($handler);
            } elseif (is_array($handler) && isset($handler['options']['formatter']) && is_string($handler['options']['formatter'])) {
                $handler['options']['formatter'] = $this->resolveFormatter($handler['options']['formatter']);
            }

            return $handler;
        }, $handlers);
    }

    private function prepareProcessors(array $processors): array
    {
        return array_map(function ($processor) {
            if (is_string($processor)) {
                $processor = $this->resolveProcessor($processor);
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
        try {
            if ($this->container->has($serviceOrFactory)) {
                return $this->container->get($serviceOrFactory);
            }

            if (class_exists($serviceOrFactory)) {
                $factory = new $serviceOrFactory();
                return $factory($this->container);
            }
        } catch (Throwable $ex) {
            throw CannotResolveLoggerComponent::resolutionFailed($serviceOrFactory, $ex);
        }

        throw CannotResolveLoggerComponent::unknownService($serviceOrFactory);
    }

    private function getLoggerFactory(): LoggerFactory
    {
        if (null === $this->loggerFactory) {
            $this->loggerFactory = new LoggerFactory();
        }

        return $this->loggerFactory;
    }
}
