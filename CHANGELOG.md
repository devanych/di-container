# Devanych Di Container Change Log

## 2.0.0 - under development

### Added

- `Devanych\Di\FactoryInterface`.
- `vimeo/psalm` package for static analysis code.
- Add a `build` action that is triggered when `push` and `pull_request` events.

### Changed

- Increases the minimum supported PHP version to 7.4.
- Updates package dependencies to composer.json.
- PSR-2 coding standard on PSR-12.
- Modifies `Devanych\Di\Container`:
    - Adds `__construct(array $definitions = [])` method; inside the constructor calls the `setAll()` method.
    - Adds automatic resolving `Devanych\Di\FactoryInterface` instances to the `Container::get()` and `Container::getNew()` methods.
    - Replaces deprecated reflection methods (`ReflectionParameter::getClass()` and `ReflectionParameter::isArray()`) to usage `ReflectionNamedType` for PHP 8.0.
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
