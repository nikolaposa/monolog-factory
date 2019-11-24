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
use MonologFactory\Helper\GenericObjectFactory;
use MonologFactory\Helper\ObjectFactory;

class LoggerFactory
{
    /** @var ObjectFactory */
    private $objectFactory;
    
    public function __construct(ObjectFactory $objectFactory = null)
    {
        $this->objectFactory = $objectFactory ?? new GenericObjectFactory(new Cascader());
    }

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

        $handler = $this->objectFactory->create($handlerConfig->getName(), $handlerConfig->getParameters());

        if ($handler instanceof ProcessableHandlerInterface) {
            foreach (array_reverse($handlerConfig->getProcessors()) as $processorConfig) {
                $handler->pushProcessor($this->createProcessor($processorConfig));
            }
        }

        if ($handler instanceof FormattableHandlerInterface && null !== ($formatterConfig = $handlerConfig->getFormatter())) {
            $handler->setFormatter($this->createFormatter($formatterConfig));
        }

        /** @noinspection PhpIncompatibleReturnTypeInspection */
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

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->objectFactory->create($processor->getName(), $processor->getParameters());
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

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->objectFactory->create($formatter->getName(), $formatter->getParameters());
    }
}
