<?php

declare(strict_types=1);

namespace MonologFactory\Tests;

use Closure;
use Monolog\Formatter\HtmlFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\ScalarFormatter;
use Monolog\Handler\BufferHandler;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Handler\NullHandler;
use Monolog\Handler\RollbarHandler;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\PsrLogMessageProcessor;
use MonologFactory\Exception\InvalidFactoryInputException;
use MonologFactory\Exception\InvalidOptionsException;
use MonologFactory\LoggerFactory;
use PHPUnit\Framework\TestCase;
use Rollbar\RollbarLogger;

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
    public function it_creates_logger_with_no_options(): void
    {
        $logger = $this->factory->createLogger('test', []);

        $this->assertSame('test', $logger->getName());
    }

    /**
     * @test
     */
    public function it_creates_logger_with_handlers_and_processors_options_as_instances(): void
    {
        $logger = $this->factory->createLogger('test', [
            'handlers' => [
                new NullHandler(),
            ],
            'processors' => [
                new PsrLogMessageProcessor(),
            ],
        ]);

        $this->assertCount(1, $logger->getHandlers());
        $this->assertCount(1, $logger->getProcessors());
        $this->assertInstanceOf(NullHandler::class, current($logger->getHandlers()));
        $this->assertInstanceOf(PsrLogMessageProcessor::class, current($logger->getProcessors()));
    }

    /**
     * @test
     */
    public function it_creates_logger_with_processor_as_callable(): void
    {
        $logger = $this->factory->createLogger('test', [
            'processors' => [
                function (array $record) {
                    return $record;
                }
            ],
        ]);

        $this->assertCount(1, $logger->getProcessors());
        $this->assertIsCallable(current($logger->getProcessors()));
    }

    /**
     * @test
     */
    public function it_creates_logger_with_handlers_and_processors_options_as_factory_inputs(): void
    {
        $logger = $this->factory->createLogger('test', [
            'handlers' => [
                [
                    'name' => NullHandler::class,
                    'options' => [
                        'level' => Logger::INFO,
                        'formatter' => new ScalarFormatter(),
                    ],
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
        $this->assertInstanceOf(NullHandler::class, $handler);
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
        $logger = $this->factory->createLogger('test', [
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
        $logger = $this->factory->createLogger('test', [
            'handlers' => [
                [
                    'name' => NullHandler::class,
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
    public function it_creates_handler_with_no_options(): void
    {
        $handler = $this->factory->createHandler(NullHandler::class);

        $this->assertInstanceOf(NullHandler::class, $handler);
    }

    /**
     * @test
     */
    public function it_creates_handler_with_options(): void
    {
        $handler = $this->factory->createHandler(NativeMailerHandler::class, [
            'to' => 'test@example.com',
            'subject' => 'Test',
            'from' => 'noreply@example.com',
        ]);

        $this->assertInstanceOf(NativeMailerHandler::class, $handler);
        $this->assertContains('test@example.com', self::readPrivateProperty($handler, 'to'));
        $this->assertSame('Test', self::readPrivateProperty($handler, 'subject'));
        $this->assertContains('From: noreply@example.com', self::readPrivateProperty($handler, 'headers'));
    }

    /**
     * @test
     */
    public function it_creates_handler_with_randomly_ordered_options(): void
    {
        /* @var $handler NativeMailerHandler */
        $handler = $this->factory->createHandler(NativeMailerHandler::class, [
            'subject' => 'Test',
            'from' => 'noreply@example.com',
            'level' => Logger::ALERT,
            'to' => 'test@example.com',
        ]);

        $this->assertInstanceOf(NativeMailerHandler::class, $handler);
        $this->assertContains('test@example.com', self::readPrivateProperty($handler, 'to'));
        $this->assertSame('Test', self::readPrivateProperty($handler, 'subject'));
        $this->assertContains('From: noreply@example.com', self::readPrivateProperty($handler, 'headers'));
        $this->assertSame(Logger::ALERT, $handler->getLevel());
    }

    /**
     * @test
     */
    public function it_creates_handler_with_nested_objects(): void
    {
        $handler = $this->factory->createHandler(RollbarHandler::class, [
            'rollbar_logger' => [
                'enabled' => false,
            ],
            'level' => Logger::ERROR,
        ]);

        $this->assertInstanceOf(RollbarHandler::class, $handler);
        $this->assertInstanceOf(RollbarLogger::class, self::readPrivateProperty($handler, 'rollbarLogger'));
    }

    /**
     * @test
     */
    public function it_creates_handler_with_interface_dependency_by_passing_concrete_class_name_through_options(): void
    {
        /* @var $handler BufferHandler */
        $handler = $this->factory->createHandler(BufferHandler::class, [
            'handler' => [
                '__class__' => NativeMailerHandler::class,
                'to' => 'test@example.com',
                'subject' => 'Test',
                'from' => 'noreply@example.com',
            ],
            'buffer_limit' => 5
        ]);

        $this->assertInstanceOf(BufferHandler::class, $handler);
        $this->assertInstanceOf(NativeMailerHandler::class, self::readPrivateProperty($handler, 'handler'));
    }

    /**
     * @test
     */
    public function it_creates_handler_with_formatter_in_options(): void
    {
        $handler = $this->factory->createHandler(NativeMailerHandler::class, [
            'to' => 'test@example.com',
            'subject' => 'Test',
            'from' => 'noreply@example.com',
            'formatter' => [
                'name' => HtmlFormatter::class,
            ],
        ]);

        $this->assertInstanceOf(NativeMailerHandler::class, $handler);
        $this->assertInstanceOf(HtmlFormatter::class, $handler->getFormatter());
    }

    /**
     * @test
     */
    public function it_creates_handler_with_processors_in_options(): void
    {
        $handler = $this->factory->createHandler(NativeMailerHandler::class, [
            'to' => 'test@example.com',
            'subject' => 'Test',
            'from' => 'noreply@example.com',
            'processors' => [
                [
                    'name' => MemoryUsageProcessor::class,
                ],
                [
                    'name' => PsrLogMessageProcessor::class,
                ],
            ],
        ]);

        $this->assertInstanceOf(NativeMailerHandler::class, $handler);
        $this->assertInstanceOf(MemoryUsageProcessor::class, $handler->popProcessor());
        $this->assertInstanceOf(PsrLogMessageProcessor::class, $handler->popProcessor());
    }

    /**
     * @test
     */
    public function it_creates_formatter_with_no_options(): void
    {
        $formatter = $this->factory->createFormatter(LineFormatter::class);

        $this->assertInstanceOf(LineFormatter::class, $formatter);
    }

    /**
     * @test
     */
    public function it_creates_formatter_with_options(): void
    {
        $formatter = $this->factory->createFormatter(LineFormatter::class, [
            'format' => "%datetime% - %channel%.%level_name%: %message% | %context% | %extra%\n",
            'date_format' => 'c',
        ]);

        $this->assertInstanceOf(LineFormatter::class, $formatter);
        $this->assertSame("%datetime% - %channel%.%level_name%: %message% | %context% | %extra%\n", self::readPrivateProperty($formatter, 'format'));
        $this->assertSame('c', self::readPrivateProperty($formatter, 'dateFormat'));
    }

    /**
     * @test
     */
    public function it_creates_formatter_with_randomly_ordered_options(): void
    {
        $formatter = $this->factory->createFormatter(LineFormatter::class, [
            'date_format' => 'c',
            'format' => "%datetime% - %channel%.%level_name%: %message% | %context% | %extra%\n",
        ]);

        $this->assertInstanceOf(LineFormatter::class, $formatter);
        $this->assertSame("%datetime% - %channel%.%level_name%: %message% | %context% | %extra%\n", self::readPrivateProperty($formatter, 'format'));
        $this->assertSame('c', self::readPrivateProperty($formatter, 'dateFormat'));
    }

    /**
     * @test
     */
    public function it_creates_processor_with_no_options(): void
    {
        $processor = $this->factory->createProcessor(PsrLogMessageProcessor::class);

        $this->assertInstanceOf(PsrLogMessageProcessor::class, $processor);
    }

    /**
     * @test
     */
    public function it_creates_processor_with_options(): void
    {
        /** @var MemoryUsageProcessor $processor */
        $processor = $this->factory->createProcessor(MemoryUsageProcessor::class, [
            'real_usage' => true,
            'use_formatting' => false,
        ]);

        $this->assertInstanceOf(MemoryUsageProcessor::class, $processor);
        $this->assertTrue(self::readPrivateProperty($processor, 'realUsage'));
        $this->assertFalse(self::readPrivateProperty($processor, 'useFormatting'));
    }

    /**
     * @test
     */
    public function it_creates_processor_with_randomly_ordered_options(): void
    {
        /** @var MemoryUsageProcessor $processor */
        $processor = $this->factory->createProcessor(MemoryUsageProcessor::class, [
            'use_formatting' => false,
            'real_usage' => true,
        ]);

        $this->assertInstanceOf(MemoryUsageProcessor::class, $processor);
        $this->assertTrue(self::readPrivateProperty($processor, 'realUsage'));
        $this->assertFalse(self::readPrivateProperty($processor, 'useFormatting'));
    }

    /**
     * @test
     */
    public function it_raises_exception_if_logger_handlers_option_is_not_valid(): void
    {
        try {
            $this->factory->createLogger('test', [
                'handlers' => 'invalid',
            ]);

            $this->fail('Exception should have been raised');
        } catch (InvalidOptionsException $ex) {
            $this->assertSame("'handlers' should be an array; string given", $ex->getMessage());
        }
    }

    /**
     * @test
     */
    public function it_raises_exception_if_logger_processors_option_is_not_valid(): void
    {
        try {
            $this->factory->createLogger('test', [
                'processors' => 'invalid',
            ]);

            $this->fail('Exception should have been raised');
        } catch (InvalidOptionsException $ex) {
            $this->assertSame("'processors' should be an array; string given", $ex->getMessage());
        }
    }

    /**
     * @test
     */
    public function it_raises_exception_if_logger_handlers_item_option_is_not_valid(): void
    {
        try {
            $this->factory->createLogger('test', [
                'handlers' => [
                    'invalid',
                ],
            ]);

            $this->fail('Exception should have been raised');
        } catch (InvalidOptionsException $ex) {
            $this->assertSame(
                "'handlers' item should be either Monolog\\Handler\\HandlerInterface instance or an factory input array; string given",
                $ex->getMessage()
            );
        }
    }

    /**
     * @test
     */
    public function it_raises_exception_if_logger_processors_item_option_is_not_valid(): void
    {
        try {
            $this->factory->createLogger('test', [
                'processors' => [
                    'invalid',
                ],
            ]);

            $this->fail('Exception should have been raised');
        } catch (InvalidOptionsException $ex) {
            $this->assertSame(
                "'processors' item should be either callable or an factory input array; string given",
                $ex->getMessage()
            );
        }
    }

    /**
     * @test
     */
    public function it_raises_exception_if_handler_formatter_option_is_not_valid(): void
    {
        try {
            $this->factory->createLogger('test', [
                'handlers' => [
                    [
                        'name' => NullHandler::class,
                        'options' => [
                            'level' => Logger::INFO,
                            'formatter' => 'invalid',
                        ],
                    ],
                ],
            ]);

            $this->fail('Exception should have been raised');
        } catch (InvalidOptionsException $ex) {
            $this->assertSame(
                "Handler 'formatter' should be either Monolog\\Formatter\\FormatterInterface instance or an factory input array; string given",
                $ex->getMessage()
            );
        }
    }

    /**
     * @test
     */
    public function it_raises_exception_for_invalid_factory_input(): void
    {
        try {
            $this->factory->createLogger('test', [
                'handlers' => [
                    [
                        'foo' => NullHandler::class,
                    ],
                ],
            ]);

            $this->fail('Exception should have been raised');
        } catch (InvalidFactoryInputException $ex) {
            $this->assertSame("'name' is missing from the factory input", $ex->getMessage());
        }
    }

    /**
     * @test
     */
    public function it_raises_exception_for_invalid_factory_input_options(): void
    {
        try {
            $this->factory->createLogger('test', [
                'handlers' => [
                    [
                        'name' => NullHandler::class,
                        'options' => 'invalid',
                    ],
                ],
            ]);

            $this->fail('Exception should have been raised');
        } catch (InvalidFactoryInputException $ex) {
            $this->assertSame("Factory input 'options' should be an array; string given", $ex->getMessage());
        }
    }
}
