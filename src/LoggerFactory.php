<?php

declare(strict_types=1);

namespace MonologFactory;

use Cascader\Cascader;
use Monolog\Logger;
use Monolog\Handler\HandlerInterface;
use Monolog\Formatter\FormatterInterface;
use MonologFactory\Options\FormatterOptions;
use MonologFactory\Options\HandlerOptions;
use MonologFactory\Options\LoggerOptions;
use MonologFactory\Options\ProcessorOptions;

class LoggerFactory
{
    /**
     * @var Cascader
     */
    private $cascader;

    public function createLogger(string $name, array $options) : Logger
    {
        $options = LoggerOptions::fromArray($options);
        
        $logger = new Logger($name);
        
        foreach ($options->getHandlers() as $handler) {
            $logger->pushHandler($this->createHandlerFromOptions($handler));
        }

        foreach ($options->getProcessors() as $processor) {
            $logger->pushProcessor($this->createProcessorFromOptions($processor));
        }

        return $logger;
    }

    public function createHandler(string $name, array $options = []) : HandlerInterface
    {
        $options = HandlerOptions::fromArray($options);

        $handler = $this->createObject($name, $options->toArray());

        if (false !== ($formatter = $options->getFormatter())) {
            $formatter = $this->createFormatterFromOptions($formatter);
            $handler->setFormatter($formatter);
        }

        return $handler;
    }

    public function createFormatter(string $name, array $options = []) : FormatterInterface
    {
        $options = FormatterOptions::fromArray($options);

        return $this->createObject($name, $options->toArray());
    }

    public function createProcessor(string $name, array $options = []) : callable
    {
        $options = ProcessorOptions::fromArray($options);

        return $this->createObject($name, $options->toArray());
    }

    protected function createHandlerFromOptions($handler) : HandlerInterface
    {
        if ($handler instanceof HandlerInterface) {
            return $handler;
        }

        $factoryInput = FactoryInput::fromArray($handler);

        return $this->createHandler($factoryInput->getName(), $factoryInput->getOptions());
    }

    protected function createFormatterFromOptions($formatter) : FormatterInterface
    {
        if ($formatter instanceof FormatterInterface) {
            return $formatter;
        }

        $factoryInput = FactoryInput::fromArray($formatter);

        return $this->createFormatter($factoryInput->getName(), $factoryInput->getOptions());
    }

    protected function createProcessorFromOptions($processor) : callable
    {
        if (is_callable($processor)) {
            return $processor;
        }

        $factoryInput = FactoryInput::fromArray($processor);

        return $this->createProcessor($factoryInput->getName(), $factoryInput->getOptions());
    }

    protected function createObject(string $className, array $creationOptions)
    {
        return $this->getCascader()->create($className, $creationOptions);
    }

    final protected function getCascader() : Cascader
    {
        if (null === $this->cascader) {
            $this->cascader = new Cascader();
        }

        return $this->cascader;
    }
}
