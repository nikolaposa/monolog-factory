<?php

declare(strict_types=1);

namespace MonologFactory\Tests;

use Interop\Container\ContainerInterface;
use Monolog\Formatter\HtmlFormatter;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Handler\NullHandler;
use Monolog\Logger;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\PsrLogMessageProcessor;
use MonologFactory\ContainerInteropLoggerFactory;
use MonologFactory\Tests\TestAsset\ContainerAsset;
use MonologFactory\Tests\TestAsset\Logger\ProcessorFactoryAsset;
use PHPUnit\Framework\TestCase;

class ContainerInteropLoggerFactoryTest extends TestCase
{
    /**
     * @var ContainerInterface
     */
    protected $container;
    
    protected function setUp()
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
    public function it_creates_logger_from_configuration()
    {
        $factory = new ContainerInteropLoggerFactory('logger1');

        /* @var $logger Logger */
        $logger = $factory($this->container);

        $this->assertInstanceOf(Logger::class, $logger);
        $this->assertEquals('logger1', $logger->getName());
        $this->assertCount(1, $logger->getHandlers());
        $this->assertCount(1, $logger->getProcessors());
    }

    /**
     * @test
     */
    public function it_creates_empty_logger_if_specified_does_not_exist_in_configuration()
    {
        $factory = new ContainerInteropLoggerFactory();

        /* @var $logger Logger */
        $logger = $factory($this->container);

        $this->assertInstanceOf(Logger::class, $logger);
        $this->assertEquals('default', $logger->getName());
        $this->assertCount(0, $logger->getHandlers());
        $this->assertCount(0, $logger->getProcessors());
    }

    /**
     * @test
     */
    public function it_creates_logger_when_invoked_using_static_variance()
    {
        $factory = [ContainerInteropLoggerFactory::class, 'logger1'];

        /* @var $logger Logger */
        $logger = call_user_func($factory, $this->container);

        $this->assertInstanceOf(Logger::class, $logger);
    }

    /**
     * @test
     */
    public function it_creates_logger_by_resolving_handler_from_container()
    {
        $factory = new ContainerInteropLoggerFactory('logger2');

        /* @var $logger Logger */
        $logger = $factory($this->container);

        $this->assertInstanceOf(Logger::class, $logger);
        $this->assertEquals('logger2', $logger->getName());
        $handlers = $logger->getHandlers();
        $this->assertCount(2, $handlers);
        $this->assertInstanceOf(NullHandler::class, $handlers[1]);
        $this->assertInstanceOf(NativeMailerHandler::class, $handlers[0]);
    }

    /**
     * @test
     */
    public function it_creates_logger_by_resolving_handler_formatter_from_container()
    {
        $factory = new ContainerInteropLoggerFactory('logger2');

        /* @var $logger Logger */
        $logger = $factory($this->container);

        $this->assertInstanceOf(Logger::class, $logger);
        $this->assertEquals('logger2', $logger->getName());
        $handlers = $logger->getHandlers();
        $this->assertCount(2, $handlers);
        $this->assertInstanceOf(NativeMailerHandler::class, $handlers[0]);
        $this->assertInstanceOf(HtmlFormatter::class, $handlers[0]->getFormatter());
    }

    /**
     * @test
     */
    public function it_creates_logger_by_resolving_processor_from_container()
    {
        $factory = new ContainerInteropLoggerFactory('logger2');

        /* @var $logger Logger */
        $logger = $factory($this->container);

        $this->assertInstanceOf(Logger::class, $logger);
        $this->assertEquals('logger2', $logger->getName());
        $processors = $logger->getProcessors();
        $this->assertCount(1, $logger->getProcessors());
        $this->assertInstanceOf(MemoryUsageProcessor::class, $processors[0]);
    }
}
