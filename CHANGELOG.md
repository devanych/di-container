# Devanych Di Container Change Log

## 2.1.0 - 2020-08-20

### Added

- Adds support for PHP 8.0.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.0.1 - 2020-08-06

### Added

- Nothing

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Fixes autowiring for the interface with default value as definition in the constructor.

## 2.0.0 - 2020-07-21

### Added

- `Devanych\Di\FactoryInterface`.
- `vimeo/psalm` package for static analysis code.
- `build` action that is triggered when `push` and `pull_request` events.

### Changed

- Increases the minimum supported PHP version to 7.4.
- Updates package dependencies to composer.json.
- PSR-2 coding standard on PSR-12.
- Modifies `Devanych\Di\Container`:
    - Adds `__construct(array $definitions = [])` method; inside the constructor calls the `setMultiple()` method.
    - Adds automatic resolving `Devanych\Di\FactoryInterface` instances to the `Container::get()` and `Container::getNew()` methods.
    - Replaces deprecated reflection methods (`ReflectionParameter::getClass()` and `ReflectionParameter::isArray()`) to usage `ReflectionNamedType` for PHP 8.0.
    - Renames `setAll()` to `setMultiple()` method.
    - Makes the class is final.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.0.1 - 2019-06-21

### Added

- `squizlabs/php_codesniffer` package for detect violations of a PSR-2 coding standard.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.0.0 - 2019-06-09

- Initial stable release.
