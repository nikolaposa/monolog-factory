<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Monolog\Formatter\HtmlFormatter;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Handler\NullHandler;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;
use MonologFactory\LoggerFactory;

$loggerFactory = new LoggerFactory();

//manual handler/processor instantiation
$logger1 = $loggerFactory->createLogger('logger1', [
    'handlers' => [
        new NullHandler(),
    ],
    'processors' => [
        new PsrLogMessageProcessor(),
    ],
]);

//plain-array handlers/processors configuration
$logger2 = $loggerFactory->createLogger('logger2', [
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
]);
