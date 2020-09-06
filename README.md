# Dependency Injection Container

[![License](https://poser.pugx.org/devanych/di-container/license)](https://packagist.org/packages/devanych/di-container)
[![Latest Stable Version](https://poser.pugx.org/devanych/di-container/v)](https://packagist.org/packages/devanych/di-container)
[![Total Downloads](https://poser.pugx.org/devanych/di-container/downloads)](https://packagist.org/packages/devanych/di-container)
[![GitHub Build Status](https://github.com/devanych/di-container/workflows/build/badge.svg)](https://github.com/devanych/di-container/actions)
[![GitHub Static Analysis Status](https://github.com/devanych/di-container/workflows/static/badge.svg)](https://github.com/devanych/di-container/actions)
[![Scrutinizer Code Coverage](https://scrutinizer-ci.com/g/devanych/di-container/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/devanych/di-container/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/devanych/di-container/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/devanych/di-container/?branch=master)

A simple and lightweight container for dependency injection using autowiring that implements [PSR-11 Container](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-11-container.md).

Support:

* Objects or class names.

* Anonymous functions (Closure instance).

* Scalars (integer, float, string, boolean).

* Arrays and nested arrays with all of the above data types.

A guide with a detailed description in Russian language is [available here](https://devanych.ru/development/prostoj-di-kontejner-s-podderzhkoj-avtovajringa).

## Installation

This package requires PHP version 7.4 or later.

```
composer require devanych/di-container
```

## Usage

Create a container:

```php
use Devanych\Di\Container;

$container = new Container();
// or with definitions array
$container = new Container($definitions);
```

Sets in the container:

```php
/**
 * Sets definition to the container.
 *
 * @param string $id
 * @param mixed $definition
 */
$container->set($id, $definition);

/**
 * Sets multiple definitions at once; used in the constructor.
 *
 * @param array<string, mixed> $definitions
 */
$container->setMultiple($definitions);
```

Existence in the container:

```php
/**
 * Returns 'true` if the dependency with this ID was sets, otherwise `false`.
 *
 * @param string $id
 * @return bool
 */
$container->has($id);
```

Gets from the container:

```php
/**
 * Gets instance by definition from the container by ID.
 *
 * @param string $id
 * @return mixed
 * @throws Devanych\Di\Exception\NotFoundException If not found definition in the container.
 * @throws Devanych\Di\Exception\ContainerException If unable to create instance.
 */
$container->get($id);

/**
 * Always gets a new instance by definition from the container by ID.
 *
 * @param string $id
 * @return mixed
 * @throws Devanych\Di\Exception\NotFoundException If not found definition in the container.
 * @throws Devanych\Di\Exception\ContainerException If unable to create instance.
 */
$container->getNew($id);

/**
 * Gets original definition from the container by ID.
 *
 * @param string $id
 * @return mixed
 * @throws Devanych\Di\Exception\NotFoundException If not found definition in the container.
 */
$container->getDefinition($id);
```
If the definition is an anonymous function or class name, the `get()` method will execute the function and create an instance of the class only the first time, and subsequent `get()` calls will return the result already created.

> If you need to execute a function and create an instance of the class every time, use the `getNew ()` method.

If the definition is a class name and there are dependencies in its constructor, then when calling the `get()` and `getNew ()` methods, at the time of creating the class instance, the container will recursively bypass all dependencies and try to resolve them.

> If the passed parameter `$id` to the methods `get()` and `getNew()` is a class name and has not been set previously by the `set()` method, an object of these class will still be created as if it had been set.
>
> If `$id` is not a class name and has not been set previously by the `set()` method, the exception `Devanych\Di\Exception\NotFoundException` will be thrown.

## Examples of use

Simple usage:

```php
// Set string
$container->set('string', 'value');
$container->get('string'); // 'value'

// Set integer
$container->set('integer', 5);
$container->get('integer'); // 5

// Set array
$container->set('array', [1,2,3]);
$container->get('array'); // [1,2,3]

// Set nested array
$container->set('nested', [
    'scalar' => [
        'integer' => 5,
        'float' => 3.7,
        'boolean' => false,
        'string' => 'string',
    ],
    'not_scalar' => [
        'closure' => fn() => null,
        'object' => new User(),
        'array' => ['array'],
    ],
]);

// Set object
$container->set('user', fn() => new User());
$container->get('user'); // User instance
// Or
$container->set('user', User::class);
$container->get('user');
// Or
$container->set(User::class, User::class);
$container->get(User::class);
// Or without setting via `set()`
$container->get(User::class);
```

Usage of dependencies:

```php
/*
final class UserProfile
{
    private $name;
    private $age;

    public function __construct(string $name = 'John', int $age = 25)
    {
        $this->name = $name;
        $this->age = $age;
    }
}

final class User
{
    private $profile;

    public function __construct(UserProfile $profile)
    {
        $this->profile = $profile;
    }
}
*/

$container->set('user_name', 'Alexander');
$container->set('user_age', 40);

$container->set('user', function (\Psr\Container\ContainerInterface $container): User {
    $name = $container->get('user_name');
    $age = $container->get('user_age');
    $profile = new UserProfile($name, $age);
    return new User($profile);
});

$container->get('user');

// Or

$container->set(UserProfile::class, function (\Psr\Container\ContainerInterface $container): UserProfile {
    return new UserProfile($container->get('user_name'), $container->get('user_age'));
});

$container->set(User::class, function (\Psr\Container\ContainerInterface $container): User {
    return new User($container->get(UserProfile::class));
});

$container->get(User::class);

// Or with default values (`John` and `25`)

$container->set(User::class, User::class);
$container->get(User::class);
```

Usage with dependencies and factories:

```php
/*
final class UserProfileFactory implements \Devanych\Di\FactoryInterface
{
    public function create(\Psr\Container\ContainerInterface $container): UserProfile
    {
        return new UserProfile($container->get('user_name'), $container->get('user_age'));
    }
}

final class UserFactory implements \Devanych\Di\FactoryInterface
{
    public ?string $name;
    public ?int $age;
    
    public function __construct(string $name = null, int $age = null)
    {
        $this->name = $name;
        $this->age = $age;
    }

    public function create(\Psr\Container\ContainerInterface $container): User
    {
        if ($this->name === null || $this->age === null) {
            return new User($container->get(UserProfile::class));
        }

        return new User(new UserProfile($this->name, $this->age));
    }   
}
*/

$container->setMultiple([
    'user_name' => 'Alexander',
    'user_age' => 40,
    User::class => UserFactory::class,
    UserProfile::class => UserProfileFactory::class,
]);

$container->get(User::class); // User instance
$container->get(UserProfile::class); // UserProfile instance
```
