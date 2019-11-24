<?php

declare(strict_types=1);

namespace MonologFactory;

use Cascader\Cascader;
use Monolog\Handler\FormattableHandlerInterface;
use Monolog\Handler\ProcessableHandlerInterface;
use Monolog\Logger;
use Monolog\Handler\HandlerInterface;
use Monolog\Formatter\FormatterInterface;
use MonologFactory\Config\FormatterConfig;
use MonologFactory\Config\HandlerConfig;
use MonologFactory\Config\LoggerConfig;
use MonologFactory\Config\ProcessorConfig;

class LoggerFactory
{
    /** @var Cascader */
    private $cascader;

    public function create(string $name, array $config = []): Logger
    {
        $config[LoggerConfig::NAME] = $name;
        $config = LoggerConfig::fromArray($config);

        return new Logger(
            $config->getName(),
            array_map([$this, 'createHandler'], $config->getHandlers()),
            array_map([$this, 'createProcessor'], $config->getProcessors()),
            $config->getTimezone()
        );
    }

    /**
     * @param HandlerInterface|HandlerConfig $handler
     * @return HandlerInterface
     */
    protected function createHandler($handler): HandlerInterface
    {
        if ($handler instanceof HandlerInterface) {
            return $handler;
        }

        $handlerConfig = $handler;

        $handler = $this->createObject($handlerConfig->getName(), $handlerConfig->getParameters());

        if ($handler instanceof ProcessableHandlerInterface) {
            foreach (array_reverse($handlerConfig->getProcessors()) as $processorConfig) {
                $handler->pushProcessor($this->createProcessor($processorConfig));
            }
        }

        if ($handler instanceof FormattableHandlerInterface && null !== ($formatterConfig = $handlerConfig->getFormatter())) {
            $handler->setFormatter($this->createFormatter($formatterConfig));
        }

        return $handler;
    }

    /**
     * @param callable|ProcessorConfig $processor
     * @return callable
     */
    protected function createProcessor($processor): callable
    {
        if (is_callable($processor)) {
            return $processor;
        }

        return $this->createObject($processor->getName(), $processor->getParameters());
    }

    /**
     * @param FormatterInterface|FormatterConfig $formatter
     * @return FormatterInterface
     */
    protected function createFormatter($formatter): FormatterInterface
    {
        if ($formatter instanceof FormatterInterface) {
            return $formatter;
        }

        return $this->createObject($formatter->getName(), $formatter->getParameters());
    }

    /**
     * @param string $className
     * @param array $creationOptions
     * @return mixed
     */
    protected function createObject(string $className, array $creationOptions)
    {
        return $this->getCascader()->create($className, $creationOptions);
    }

    final protected function getCascader(): Cascader
    {
        if (null === $this->cascader) {
            $this->cascader = new Cascader();
        }

        return $this->cascader;
    }
}
