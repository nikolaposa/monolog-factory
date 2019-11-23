<?php declare(strict_types=1);

use Monolog\Formatter\HtmlFormatter;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Handler\NullHandler;
use Monolog\Logger;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\PsrLogMessageProcessor;
use MonologFactory\DiContainerLoggerFactory;

return [
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
                'DefaultLoggerHandler', //service name
                [
                    'name' => NativeMailerHandler::class,
                    'options' => [
                        'to' => 'test@example.com',
                        'subject' => 'Test',
                        'from' => 'noreply@example.com',
                        'level' => Logger::ALERT,
                        'formatter' => 'HtmlLoggerFormatter', //service name
                    ],
                ],
            ],
            'processors' => [
                'MemoryUsageProcessor', //service name
            ],
        ],
    ],
    'di' => [
        'Logger1' => new DiContainerLoggerFactory('logger1'),
        'Logger2' => [DiContainerLoggerFactory::class, 'logger2'], //static variance; recommended for having plain-array configurations
        'DefaultLoggerHandler' => function () {
            return new NullHandler();
        },
        'HtmlLoggerFormatter' => function () {
            return new HtmlFormatter();
        },
        'MemoryUsageLoggerProcessor' => function () {
            return new MemoryUsageProcessor();
        },
    ],
];
