# Monolog Factory

[![Build Status][ico-build]][link-build]
[![Code Quality][ico-code-quality]][link-code-quality]
[![Code Coverage][ico-code-coverage]][link-code-coverage]
[![Latest Version][ico-version]][link-packagist]
[![PDS Skeleton][ico-pds]][link-pds]

Monolog Factory facilitates creation of [Monolog][link-monolog] logger objects.

Besides the generic factory, this package features one that is suitable for using Monolog with [container-interop][link-container-interop].

## Installation

The preferred method of installation is via [Composer](http://getcomposer.org/). Run the following command to install the latest version of a package and add it to your project's `composer.json`:

```bash
composer require nikolaposa/monolog-factory
```

## Usage

**Generic factory**

``` php
use Monolog\Formatter\HtmlFormatter;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;
use MonologFactory\LoggerFactory;

$loggerFactory = new LoggerFactory();

$logger = $loggerFactory->createLogger('my_logger', [
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
```

**Container-interop factory configuration**

```php
use Monolog\Formatter\HtmlFormatter;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;
use MonologFactory\ContainerInteropLoggerFactory;

return [
    'logger' => [
        'my_logger' => [
            'name' => 'my_logger',
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
    'di' => [
        'factories' => [
            'MyLogger1' => new ContainerInteropLoggerFactory('my_logger'),
            //... or more preferred/optimal way:
            'MyLogger2' => [ContainerInteropLoggerFactory::class, 'my_logger'],
        ],
    ],
];
```

See [more examples][link-examples].

## Credits

- [Nikola Poša][link-author]
- [All Contributors][link-contributors]

## License

Released under MIT License - see the [License File](LICENSE) for details.


[ico-version]: https://img.shields.io/packagist/v/nikolaposa/monolog-factory.svg
[ico-build]: https://travis-ci.org/nikolaposa/monolog-factory.svg?branch=master
[ico-code-coverage]: https://img.shields.io/scrutinizer/coverage/g/nikolaposa/monolog-factory.svg
[ico-code-quality]: https://img.shields.io/scrutinizer/g/nikolaposa/monolog-factory.svg
[ico-pds]: https://img.shields.io/badge/pds-skeleton-blue.svg

[link-monolog]: https://github.com/Seldaek/monolog
[link-container-interop]: https://github.com/container-interop/container-interop
[link-examples]: examples
[link-packagist]: https://packagist.org/packages/nikolaposa/monolog-factory
[link-build]: https://travis-ci.org/nikolaposa/monolog-factory
[link-code-coverage]: https://scrutinizer-ci.com/g/nikolaposa/monolog-factory/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/nikolaposa/monolog-factory
[link-pds]: https://github.com/php-pds/skeleton
[link-author]: https://github.com/nikolaposa
[link-contributors]: ../../contributors
