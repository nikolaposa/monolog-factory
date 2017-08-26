# Change Log

All notable changes to this project will be documented in this file.

## 2.0.0 - [Unreleased]

### Added
-  `AbstractDiContainerLoggerFactory` to allow for having a custom logger config resolution strategy.

### Changed
- Rename `ContainerInteropLoggerFactory` to `DiContainerLoggerFactory`.

## 1.0.0 - 2017-06-15

### Changed
- Improved error handling in ContainerInteropLoggerFactory.

### Fixed
- Proper ordering of handlers and processors.
- `ContainerInteropLoggerFactory` resolves logger configuration from either `Config` and `config` container entries.

## 0.1.0 - 2017-06-11


[Unreleased]: https://github.com/nikolaposa/phoundation/compare/1.1.0...HEAD
