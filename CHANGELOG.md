# Change Log

All notable changes to this project will be documented in this file.

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


[Unreleased]: https://github.com/nikolaposa/monolog-factory/compare/2.0.1...HEAD
