<?php

declare(strict_types=1);

namespace MonologFactory\Tests;

use Monolog\Formatter\HtmlFormatter;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Handler\NullHandler;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\PsrLogMessageProcessor;
use MonologFactory\DiContainerLoggerFactory;
use MonologFactory\Exception\BadStaticDiContainerFactoryUsage;
use MonologFactory\Exception\CannotResolveLoggerComponent;
use MonologFactory\Tests\TestAsset\ContainerAsset;
use MonologFactory\Tests\TestAsset\Logger\ProcessorFactoryAsset;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class DiContainerLoggerFactoryTest extends TestCase
{
    /** @var ContainerInterface */
    protected $container;
    
    protected function setUp(): void
    {
        $this->container = new ContainerAsset([
            'Config' => [
                'logger' => [
                    'logger1' => [
                        'name' => 'logger1',
                        'handlers' => [
                            [
                                'name' => NativeMailerHandler::class,
                                'options' => [
                                    'to' => 'test@example.com',
                                    'subject' => 'Test',
                                    'from' => 'noreply@example.com',
                                    'level' => Logger::ALERT,
                                    'formatter' => [
                                        'name' => HtmlFormatter::class,
                                    ],
                                ],
                            ],
                        ],
                        'processors' => [
                            [
                                'name' => PsrLogMessageProcessor::class,
                            ],
                        ],
                    ],
                    'logger2' => [
                        'name' => 'logger2',
                        'handlers' => [
                            'DefaultLoggerHandler',
                            [
                                'name' => NativeMailerHandler::class,
                                'options' => [
                                    'to' => 'test@example.com',
                                    'subject' => 'Test',
                                    'from' => 'noreply@example.com',
                                    'level' => Logger::ALERT,
                                    'formatter' => 'HtmlLoggerFormatter',
                                ],
                            ],
                        ],
                        'processors' => [
                            ProcessorFactoryAsset::class,
                        ],
                    ],
                    'invalid_handler_logger' => [
                        'name' => 'invalid_handler_logger',
                        'handlers' => [
                            'NonExistingHandler',
                        ],
                    ],
                    'invalid_formatter_logger' => [
                        'name' => 'invalid_handler_logger',
                        'handlers' => [
                            [
                                'name' => NullHandler::class,
                                'options' => [
                                    'formatter' => 'NonExistingFormatter',
                                ],
                            ],
                        ],
                    ],
                    'invalid_processor_logger' => [
                        'name' => 'invalid_processor_logger',
                        'handlers' => [
                            [
                                'name' => NullHandler::class,
                            ],
                        ],
                        'processors' => [
                            'NonExistingProcessor',
                        ],
                    ]
                ],
            ],
            'DefaultLoggerHandler' => new NullHandler(),
            'HtmlLoggerFormatter' => new HtmlFormatter(),
            'MemoryUsageLoggerProcessor' => new MemoryUsageProcessor(),
        ]);
    }

    /**
     * @test
     */
    public function it_creates_logger_from_configuration(): void
    {
        $factory = new DiContainerLoggerFactory('logger1');

        $logger = $factory($this->container);

        $this->assertSame('logger1', $logger->getName());
        $this->assertCount(1, $logger->getHandlers());
        $this->assertCount(1, $logger->getProcessors());
    }

    /**
     * @test
     */
    public function it_creates_logger_from_alias_configuration_service(): void
    {
        $factory = new DiContainerLoggerFactory('logger3');

        $logger = $factory(new ContainerAsset([
            'config' => [
                'logger' => [
                    'logger3' => [
                        'name' => 'logger3',
                        'handlers' => [
                            [
                                'name' => TestHandler::class,
                            ],
                        ],
                    ],
                ],
            ],
        ]));

        $this->assertSame('logger3', $logger->getName());
    }

    /**
     * @test
     */
    public function it_creates_empty_logger_if_specified_does_not_exist_in_configuration(): void
    {
        $factory = new DiContainerLoggerFactory();

        $logger = $factory($this->container);

        $this->assertSame('default', $logger->getName());
        $this->assertCount(0, $logger->getHandlers());
        $this->assertCount(0, $logger->getProcessors());
    }

    /**
     * @test
     */
    public function it_creates_logger_when_invoked_using_static_variance(): void
    {
        $factory = [DiContainerLoggerFactory::class, 'logger1'];

        $logger = $factory($this->container);

        $this->assertInstanceOf(Logger::class, $logger);
    }

    /**
     * @test
     */
    public function it_raises_exception_if_container_not_passed_in_arguments_when_invoked_using_static_variance(): void
    {
        $factory = [DiContainerLoggerFactory::class, 'logger1'];

        try {
            $factory('invalid');

            $this->fail('Exception should have been raised');
        } catch (BadStaticDiContainerFactoryUsage $ex) {
            $this->assertSame(
                'The first argument for ' . DiContainerLoggerFactory::class . ' must be ' . ContainerInterface::class . ' implementation',
                $ex->getMessage()
            );
        }
    }

    /**
     * @test
     */
    public function it_creates_logger_by_resolving_handler_from_container(): void
    {
        $factory = new DiContainerLoggerFactory('logger2');

        $logger = $factory($this->container);

        $this->assertSame('logger2', $logger->getName());
        $handlers = $logger->getHandlers();
        $this->assertCount(2, $handlers);
        $this->assertInstanceOf(NullHandler::class, $handlers[0]);
        $this->assertInstanceOf(NativeMailerHandler::class, $handlers[1]);
    }

    /**
     * @test
     */
    public function it_raises_exception_if_handler_cannot_be_resolved_from_container(): void
    {
        $factory = new DiContainerLoggerFactory('invalid_handler_logger');

        try {
            $factory($this->container);

            $this->fail('Exception should have been raised');
        } catch (CannotResolveLoggerComponent $ex) {
            $this->assertStringContainsString("Cannot resolve 'NonExistingHandler'", $ex->getMessage());
        }
    }

    /**
     * @test
     */
    public function it_raises_exception_if_formatter_cannot_be_resolved_from_container(): void
    {
        $factory = new DiContainerLoggerFactory('invalid_formatter_logger');

        try {
            $factory($this->container);

            $this->fail('Exception should have been raised');
        } catch (CannotResolveLoggerComponent $ex) {
            $this->assertStringContainsString("Cannot resolve 'NonExistingFormatter'", $ex->getMessage());
        }
    }

    /**
     * @test
     */
    public function it_raises_exception_if_processor_cannot_be_resolved_from_container(): void
    {
        $factory = new DiContainerLoggerFactory('invalid_processor_logger');

        try {
            $factory($this->container);

            $this->fail('Exception should have been raised');
        } catch (CannotResolveLoggerComponent $ex) {
            $this->assertStringContainsString("Cannot resolve 'NonExistingProcessor'", $ex->getMessage());
        }
    }

    /**
     * @test
     */
    public function it_creates_logger_by_resolving_handler_formatter_from_container(): void
    {
        $factory = new DiContainerLoggerFactory('logger2');

        $logger = $factory($this->container);

        $this->assertSame('logger2', $logger->getName());
        $handlers = $logger->getHandlers();
        $this->assertCount(2, $handlers);
        $this->assertInstanceOf(NativeMailerHandler::class, $handlers[1]);
        $this->assertInstanceOf(HtmlFormatter::class, $handlers[1]->getFormatter());
    }

    /**
     * @test
     */
    public function it_creates_logger_by_resolving_processor_from_container(): void
    {
        $factory = new DiContainerLoggerFactory('logger2');

        $logger = $factory($this->container);

        $this->assertSame('logger2', $logger->getName());
        $processors = $logger->getProcessors();
        $this->assertCount(1, $logger->getProcessors());
        $this->assertInstanceOf(MemoryUsageProcessor::class, $processors[0]);
    }
}
