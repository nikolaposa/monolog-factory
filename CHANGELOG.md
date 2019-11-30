# Change Log

All notable changes to this project will be documented in this file.

## 3.0.0 - 2019-11-30
### Added
- Support for Monolog v2
- Support for configuring Logger timezone

### Changed
- PHP 7.2 is now the minimum required version
- Monolog 2.0 is now the minimum required version
- Use PSR-11 instead of ContainerInterop
- Rename `LoggerFactory::createLogger()` to `LoggerFactory::create()`
- Reformulate Options into Config
- Rename `options` configuration key to `params`
- Handler-level `processors` and `formatter` must be supplied as distinct configuration keys (out of `params`)

### Removed
- `LoggerFactory::createHandler()`
- `LoggerFactory::createProcessor()`
- `LoggerFactory::createFormatter()`

## 2.0.2 - 2018-10-08
### Fixed
- [4: Cannot define configuration for GroupHandler](https://github.com/nikolaposa/monolog-factory/issues/4)

## 2.0.1 - 2018-09-22
### Fixed
- [3: Cannot define logger configuration for handlers that depend on interfaces or abstract classes in their constructors](https://github.com/nikolaposa/monolog-factory/issues/3)

## 2.0.0 - 2017-09-22
### Added
-  `AbstractDiContainerLoggerFactory` to allow for having a custom logger config resolution strategy.
-  Possibility for defining per-handler processors.

### Changed
- Rename `ContainerInteropLoggerFactory` to `DiContainerLoggerFactory`.

## 1.0.0 - 2017-06-15
### Changed
- Improved error handling in ContainerInteropLoggerFactory.

### Fixed
- Proper ordering of handlers and processors.
- `ContainerInteropLoggerFactory` resolves logger configuration from either `Config` and `config` container entries.

## 0.1.0 - 2017-06-11


[Unreleased]: https://github.com/nikolaposa/monolog-factory/compare/3.0.0...HEAD
