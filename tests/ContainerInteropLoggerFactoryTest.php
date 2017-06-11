<?php

declare(strict_types=1);

namespace MonologFactory\Tests;

use Interop\Container\ContainerInterface;
use Monolog\Formatter\HtmlFormatter;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;
use MonologFactory\ContainerInteropLoggerFactory;
use MonologFactory\Tests\TestAsset\ContainerAsset;
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
                ],
            ],
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
}
