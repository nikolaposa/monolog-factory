<?php

declare(strict_types=1);

namespace MonologFactory;

use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use MonologFactory\Config\HandlerConfig;
use MonologFactory\Config\LoggerConfig;
use MonologFactory\Exception\BadStaticDiContainerFactoryUsage;
use MonologFactory\Exception\CannotResolveLoggerComponent;
use Psr\Container\ContainerInterface;
use Throwable;

abstract class AbstractDiContainerLoggerFactory
{
    /** @var string */
    protected $loggerName;

    /** @var LoggerFactory */
    private static $loggerFactory;

    /** @var ContainerInterface */
    private $container;

    public function __construct(string $loggerName = 'default')
    {
        $this->loggerName = $loggerName;
    }

    public function __invoke(ContainerInterface $container): Logger
    {
        $this->container = $container;

        $loggerConfig = array_merge([
            LoggerConfig::NAME => $this->loggerName,
            LoggerConfig::HANDLERS => [],
            LoggerConfig::PROCESSORS => [],
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
        if (is_array($config[LoggerConfig::HANDLERS])) {
            $config[LoggerConfig::HANDLERS] = $this->prepareHandlers($config[LoggerConfig::HANDLERS]);
        }

        if (is_array($config[LoggerConfig::PROCESSORS])) {
            $config[LoggerConfig::PROCESSORS] = $this->prepareProcessors($config[LoggerConfig::PROCESSORS]);
        }

        return static::getLoggerFactory()->create($config[LoggerConfig::NAME], $config);
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
            } elseif (is_array($handler) && isset($handler[HandlerConfig::FORMATTER]) && is_string($handler[HandlerConfig::FORMATTER])) {
                $handler[HandlerConfig::FORMATTER] = $this->resolveFormatter($handler[HandlerConfig::FORMATTER]);
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

    protected static function getLoggerFactory(): LoggerFactory
    {
        if (null === self::$loggerFactory) {
            self::$loggerFactory = new LoggerFactory();
        }

        return self::$loggerFactory;
    }
}
