<?php

declare(strict_types=1);

namespace MonologFactory\Tests;

use Monolog\Formatter\HtmlFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\ScalarFormatter;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Handler\NullHandler;
use Monolog\Logger;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\PsrLogMessageProcessor;
use MonologFactory\Exception\InvalidFactoryInputException;
use MonologFactory\Exception\InvalidOptionsException;
use MonologFactory\LoggerFactory;
use PHPUnit\Framework\TestCase;

class LoggerFactoryTest extends TestCase
{
    /**
     * @var LoggerFactory
     */
    protected $loggerFactory;

    protected function setUp()
    {
        $this->loggerFactory = new LoggerFactory();
    }

    /**
     * @test
     */
    public function it_creates_logger_with_no_options()
    {
        $logger = $this->loggerFactory->createLogger('test', []);

        $this->assertInstanceOf(Logger::class, $logger);
        $this->assertEquals('test', $logger->getName());
    }

    /**
     * @test
     */
    public function it_creates_logger_with_handlers_and_processors_options_as_instances()
    {
        $logger = $this->loggerFactory->createLogger('test', [
            'handlers' => [
                new NullHandler(),
            ],
            'processors' => [
                new PsrLogMessageProcessor(),
            ],
        ]);

        $this->assertInstanceOf(Logger::class, $logger);
        $this->assertCount(1, $logger->getHandlers());
        $this->assertCount(1, $logger->getProcessors());
        $this->assertInstanceOf(NullHandler::class, current($logger->getHandlers()));
        $this->assertInstanceOf(PsrLogMessageProcessor::class, current($logger->getProcessors()));
    }

    /**
     * @test
     */
    public function it_creates_logger_with_processor_as_callable()
    {
        $logger = $this->loggerFactory->createLogger('test', [
            'processors' => [
                function (array $record) {
                    return $record;
                }
            ],
        ]);

        $this->assertInstanceOf(Logger::class, $logger);
        $this->assertCount(1, $logger->getProcessors());
        $this->assertInternalType('callable', current($logger->getProcessors()));
    }

    /**
     * @test
     */
    public function it_creates_logger_with_handlers_and_processors_options_as_factory_inputs()
    {
        $logger = $this->loggerFactory->createLogger('test', [
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

        $this->assertInstanceOf(Logger::class, $logger);
        $this->assertCount(1, $logger->getHandlers());
        $this->assertCount(1, $logger->getProcessors());

        $handler = current($logger->getHandlers());
        $this->assertInstanceOf(NullHandler::class, $handler);
        $this->assertEquals(Logger::INFO, $handler->getLevel());
        $this->assertInstanceOf(ScalarFormatter::class, $handler->getFormatter());

        $processor = current($logger->getProcessors());
        $this->assertInstanceOf(PsrLogMessageProcessor::class, $processor);
    }

    /**
     * @test
     */
    public function it_creates_handler_with_no_options()
    {
        $handler = $this->loggerFactory->createHandler(NullHandler::class);

        $this->assertInstanceOf(NullHandler::class, $handler);
    }

    /**
     * @test
     */
    public function it_creates_handler_with_options()
    {
        $handler = $this->loggerFactory->createHandler(NativeMailerHandler::class, [
            'to' => 'test@example.com',
            'subject' => 'Test',
            'from' => 'noreply@example.com',
        ]);

        $this->assertInstanceOf(NativeMailerHandler::class, $handler);
        $this->assertAttributeContains('test@example.com', 'to', $handler);
        $this->assertAttributeEquals('Test', 'subject', $handler);
        $this->assertAttributeContains('From: noreply@example.com', 'headers', $handler);
    }

    /**
     * @test
     */
    public function it_creates_handler_with_randomly_ordered_options()
    {
        /* @var $handler NativeMailerHandler */
        $handler = $this->loggerFactory->createHandler(NativeMailerHandler::class, [
            'subject' => 'Test',
            'from' => 'noreply@example.com',
            'level' => Logger::ALERT,
            'to' => 'test@example.com',
        ]);

        $this->assertInstanceOf(NativeMailerHandler::class, $handler);
        $this->assertAttributeContains('test@example.com', 'to', $handler);
        $this->assertAttributeEquals('Test', 'subject', $handler);
        $this->assertAttributeContains('From: noreply@example.com', 'headers', $handler);
        $this->assertEquals(Logger::ALERT, $handler->getLevel());
    }

    /**
     * @test
     */
    public function it_creates_handler_with_formatter_in_options()
    {
        $handler = $this->loggerFactory->createHandler(NativeMailerHandler::class, [
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
    public function it_creates_formatter_with_no_options()
    {
        $formatter = $this->loggerFactory->createFormatter(LineFormatter::class);

        $this->assertInstanceOf(LineFormatter::class, $formatter);
    }

    /**
     * @test
     */
    public function it_creates_formatter_with_options()
    {
        $formatter = $this->loggerFactory->createFormatter(LineFormatter::class, [
            'format' => "%datetime% - %channel%.%level_name%: %message% | %context% | %extra%\n",
            'date_format' => 'c',
        ]);

        $this->assertInstanceOf(LineFormatter::class, $formatter);
        $this->assertAttributeEquals("%datetime% - %channel%.%level_name%: %message% | %context% | %extra%\n", 'format', $formatter);
        $this->assertAttributeEquals('c', 'dateFormat', $formatter);
    }

    /**
     * @test
     */
    public function it_creates_formatter_with_randomly_ordered_options()
    {
        $formatter = $this->loggerFactory->createFormatter(LineFormatter::class, [
            'date_format' => 'c',
            'format' => "%datetime% - %channel%.%level_name%: %message% | %context% | %extra%\n",
        ]);

        $this->assertInstanceOf(LineFormatter::class, $formatter);
        $this->assertAttributeEquals("%datetime% - %channel%.%level_name%: %message% | %context% | %extra%\n", 'format', $formatter);
        $this->assertAttributeEquals('c', 'dateFormat', $formatter);
    }

    /**
     * @test
     */
    public function it_creates_processor_with_no_options()
    {
        $processor = $this->loggerFactory->createProcessor(PsrLogMessageProcessor::class);

        $this->assertInstanceOf(PsrLogMessageProcessor::class, $processor);
    }

    /**
     * @test
     */
    public function it_creates_processor_with_options()
    {
        $processor = $this->loggerFactory->createProcessor(MemoryUsageProcessor::class, [
            'real_usage' => true,
            'use_formatting' => false,
        ]);

        $this->assertInstanceOf(MemoryUsageProcessor::class, $processor);
        $this->assertAttributeEquals(true, 'realUsage', $processor);
        $this->assertAttributeEquals(false, 'useFormatting', $processor);
    }

    /**
     * @test
     */
    public function it_creates_processor_with_randomly_ordered_options()
    {
        $processor = $this->loggerFactory->createProcessor(MemoryUsageProcessor::class, [
            'use_formatting' => false,
            'real_usage' => true,
        ]);

        $this->assertInstanceOf(MemoryUsageProcessor::class, $processor);
        $this->assertAttributeEquals(true, 'realUsage', $processor);
        $this->assertAttributeEquals(false, 'useFormatting', $processor);
    }

    /**
     * @test
     */
    public function it_raises_exception_if_logger_handlers_option_is_not_valid()
    {
        try {
            $this->loggerFactory->createLogger('test', [
                'handlers' => 'invalid',
            ]);

            $this->fail('Exception should have been raised');
        } catch (InvalidOptionsException $ex) {
            $this->assertEquals("'handlers' should be an array; string given", $ex->getMessage());
        }
    }

    /**
     * @test
     */
    public function it_raises_exception_if_logger_processors_option_is_not_valid()
    {
        try {
            $this->loggerFactory->createLogger('test', [
                'processors' => 'invalid',
            ]);

            $this->fail('Exception should have been raised');
        } catch (InvalidOptionsException $ex) {
            $this->assertEquals("'processors' should be an array; string given", $ex->getMessage());
        }
    }

    /**
     * @test
     */
    public function it_raises_exception_if_logger_handlers_item_option_is_not_valid()
    {
        try {
            $this->loggerFactory->createLogger('test', [
                'handlers' => [
                    'invalid',
                ],
            ]);

            $this->fail('Exception should have been raised');
        } catch (InvalidOptionsException $ex) {
            $this->assertEquals(
                "'handlers' item should be either Monolog\\Handler\\HandlerInterface instance or an factory input array; string given",
                $ex->getMessage()
            );
        }
    }

    /**
     * @test
     */
    public function it_raises_exception_if_logger_processors_item_option_is_not_valid()
    {
        try {
            $this->loggerFactory->createLogger('test', [
                'processors' => [
                    'invalid',
                ],
            ]);

            $this->fail('Exception should have been raised');
        } catch (InvalidOptionsException $ex) {
            $this->assertEquals(
                "'processors' item should be either callable or an factory input array; string given",
                $ex->getMessage()
            );
        }
    }

    /**
     * @test
     */
    public function it_raises_exception_if_handler_formatter_option_is_not_valid()
    {
        try {
            $this->loggerFactory->createLogger('test', [
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
            $this->assertEquals(
                "Handler 'formatter' should be either Monolog\\Formatter\\FormatterInterface instance or an factory input array; string given",
                $ex->getMessage()
            );
        }
    }

    /**
     * @test
     */
    public function it_raises_exception_for_invalid_factory_input()
    {
        try {
            $this->loggerFactory->createLogger('test', [
                'handlers' => [
                    [
                        'foo' => NullHandler::class,
                    ],
                ],
            ]);

            $this->fail('Exception should have been raised');
        } catch (InvalidFactoryInputException $ex) {
            $this->assertEquals("'name' is missing from the factory input", $ex->getMessage());
        }
    }

    /**
     * @test
     */
    public function it_raises_exception_for_invalid_factory_input_options()
    {
        try {
            $this->loggerFactory->createLogger('test', [
                'handlers' => [
                    [
                        'name' => NullHandler::class,
                        'options' => 'invalid',
                    ],
                ],
            ]);

            $this->fail('Exception should have been raised');
        } catch (InvalidFactoryInputException $ex) {
            $this->assertEquals("Factory input 'options' should be an array; string given", $ex->getMessage());
        }
    }
}
