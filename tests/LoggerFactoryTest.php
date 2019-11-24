<?php

declare(strict_types=1);

namespace MonologFactory\Tests;

use Closure;
use Monolog\Formatter\ScalarFormatter;
use Monolog\Handler\NullHandler;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\PsrLogMessageProcessor;
use MonologFactory\Exception\InvalidConfig;
use MonologFactory\LoggerFactory;
use PHPUnit\Framework\TestCase;

class LoggerFactoryTest extends TestCase
{
    /** @var LoggerFactory */
    protected $factory;

    protected function setUp(): void
    {
        $this->factory = new LoggerFactory();
    }

    final protected static function readPrivateProperty(object $object, string $property)
    {
        $reader = function ($object, $property) {
            $value = Closure::bind(function & () use ($property) {
                return $this->$property;
            }, $object, $object)->__invoke();

            return $value;
        };

        return $reader($object, $property);
    }

    /**
     * @test
     */
    public function it_creates_logger_with_no_configuration(): void
    {
        $logger = $this->factory->create('test');

        $this->assertSame('test', $logger->getName());
    }

    /**
     * @test
     */
    public function it_creates_logger_with_handler_and_processor_configurations(): void
    {
        $logger = $this->factory->create('test', [
            'handlers' => [
                [
                    'name' => TestHandler::class,
                    'params' => [
                        'level' => Logger::INFO,
                    ],
                    'formatter' => new ScalarFormatter(),
                ],
            ],
            'processors' => [
                [
                    'name' => PsrLogMessageProcessor::class,
                ],
            ],
        ]);

        $this->assertCount(1, $logger->getHandlers());
        $this->assertCount(1, $logger->getProcessors());

        $handler = current($logger->getHandlers());
        $this->assertInstanceOf(TestHandler::class, $handler);
        $this->assertSame(Logger::INFO, $handler->getLevel());
        $this->assertInstanceOf(ScalarFormatter::class, $handler->getFormatter());

        $processor = current($logger->getProcessors());
        $this->assertInstanceOf(PsrLogMessageProcessor::class, $processor);
    }

    /**
     * @test
     */
    public function it_properly_orders_handlers_when_creating_logger(): void
    {
        $logger = $this->factory->create('test', [
            'handlers' => [
                [
                    'name' => TestHandler::class,
                ],
                [
                    'name' => NullHandler::class,
                ],
            ],
            'processors' => [
                [
                    'name' => PsrLogMessageProcessor::class,
                ],
            ],
        ]);

        $handlers = $logger->getHandlers();
        $this->assertCount(2, $handlers);
        $this->assertInstanceOf(TestHandler::class, $handlers[0]);
        $this->assertInstanceOf(NullHandler::class, $handlers[1]);
    }

    /**
     * @test
     */
    public function it_properly_orders_processors_when_creating_logger(): void
    {
        $logger = $this->factory->create('test', [
            'handlers' => [
                [
                    'name' => TestHandler::class,
                ],
            ],
            'processors' => [
                [
                    'name' => MemoryUsageProcessor::class,
                ],
                [
                    'name' => PsrLogMessageProcessor::class,
                ],
            ],
        ]);

        $processors = $logger->getProcessors();
        $this->assertCount(2, $processors);
        $this->assertInstanceOf(MemoryUsageProcessor::class, $processors[0]);
        $this->assertInstanceOf(PsrLogMessageProcessor::class, $processors[1]);
    }

    /**
     * @test
     */
    public function it_raises_exception_if_handlers_configuration_is_not_valid(): void
    {
        try {
            $this->factory->create('test', [
                'handlers' => 'invalid',
            ]);

            $this->fail('Exception should have been raised');
        } catch (InvalidConfig $ex) {
            $this->assertSame("'handlers' must be an array", $ex->getMessage());
        }
    }

    /**
     * @test
     */
    public function it_raises_exception_if_processors_configuration_is_not_valid(): void
    {
        try {
            $this->factory->create('test', [
                'processors' => 'invalid',
            ]);

            $this->fail('Exception should have been raised');
        } catch (InvalidConfig $ex) {
            $this->assertSame("'processors' must be an array", $ex->getMessage());
        }
    }

    /**
     * @test
     */
    public function it_raises_exception_if_handlers_configuration_structure_is_not_valid(): void
    {
        try {
            $this->factory->create('test', [
                'handlers' => [
                    'invalid',
                ],
            ]);

            $this->fail('Exception should have been raised');
        } catch (InvalidConfig $ex) {
            $this->assertSame(
                "'handlers' must be an array of Handler instances or configuration arrays",
                $ex->getMessage()
            );
        }
    }

    /**
     * @test
     */
    public function it_raises_exception_if_processors_configuration_structure_is_not_valid(): void
    {
        try {
            $this->factory->create('test', [
                'processors' => [
                    'invalid',
                ],
            ]);

            $this->fail('Exception should have been raised');
        } catch (InvalidConfig $ex) {
            $this->assertSame(
                "'processors' must be an array of callables or configuration arrays",
                $ex->getMessage()
            );
        }
    }

    /**
     * @test
     */
    public function it_raises_exception_if_handler_formatter_configuration_is_not_valid(): void
    {
        try {
            $this->factory->create('test', [
                'handlers' => [
                    [
                        'name' => TestHandler::class,
                        'params' => [
                            'level' => Logger::INFO,
                        ],
                        'formatter' => 'invalid',
                    ],
                ],
            ]);

            $this->fail('Exception should have been raised');
        } catch (InvalidConfig $ex) {
            $this->assertSame(
                "'formatter' must be Formatter instance or configuration array",
                $ex->getMessage()
            );
        }
    }
}
