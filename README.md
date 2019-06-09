# Dependency Injection Container

PHP package. Implementation of a dependency injection [PSR-11 Container](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-11-container.md).

Simple and lightweight container using autowiring.

This package requires PHP version 7.2 or later.

Support:

* objects or class names

* anonymous functions (Closure instance)

* scalars (integer, float, string, boolean)

* arrays and nested arrays with all of the above data types

## Installation

This library is installed using the composer:

```
composer require devanych/di-container
```

## Usage

Create a container:

```php
use Devanych\Di\Container;

$container = new Container();
```

Sets in container:

```php
/**
 * Sets definition to the container.
 *
 * @param string $id
 * @param mixed $definition
 */
$container->set($id, $definition);

/**
 * Sets multiple definitions at once.
 *
 * @param array $definitions
 */
$container->setAll($definitions);
```

Has in container:

```php
/**
 * Returns `true` if the container can return an definition for this ID, otherwise `false`.
 *
 * @param string $id
 * @return bool
 */
$container->has($id);
```

Gets from container:

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
If the definition is an anonymous function or class name, the `get()` method will run the function and create an instance of the class only the first time, and subsequent `get()` calls will return the result already created.

> If you need to run the function and create an instance of the class every time, use the `getNew()` method.

If the definition is a class name and there are dependencies in its constructor, then when calling the `get()` and `getNew ()` methods, at the time of creating the class instance, the container will recursively bypass all dependencies and try to resolve them.

> If the passed parameter `$id` to the methods `get()` and `getNew()` is a class name and has not been set previously by the `set()` method, the object will still be created as if it had been set. 
>
> If `$id` is not a class name and has not been set previously by the `set()` method, the exception `Devanych\Di\Exception\NotFoundException` will be thrown.

## Examples of use

Simplest use:

```php
// Set string
$container->set('string', 'value');
// Result: 'value'
$container->get('string');

// Set integer
$container->set('integer', 5);
// Result: 5
$container->get('integer');

// Set array
$container->set('array', [1,2,3]);
// Result: [1,2,3]
$container->get('array');

// Set nested array
$container->set('nested', [
    'scalar' => [
        'integer' => 5,
        'float' => 3.7,
        'boolean' => false,
        'string' => 'string',
    ],
    'not_scalar' => [
        'closure' => function () {return;},
        'object' => new User(),
        'array' => ['array'],
    ],
]);

// Set object
$container->set('user', function () {
    return new User();
});
$container->get('user');
// Or
$container->set('user', User::class);
$container->get('user');
// Or
$container->set(User::class, User::class);
$container->get(User::class);
// Or without setting via `set()`
$container->get(User::class);
```

Use with dependencies:

```php
/*
class User
{
    private $profile;

    public function __construct(UserProfile $profile)
    {
        $this->profile = $profile;
    }
}

class UserProfile
{
    private $name;
    private $age;

    public function __construct(string $name = 'John', int $age = 25)
    {
        $this->name = $name;
        $this->age = $age;
    }
}
*/

$container->set('user_name', 'Alexander');
$container->set('user_age', 40);

$container->set('user', function (\Psr\Container\ContainerInterface $container) {
    $name = $container->get('user_name');
    $age = $container->get('user_age');
    $profile = new UserProfile($name, $age);
    return new User($profile);
});

$container->get('user');

// Or

$container->set(UserProfile::class, function (\Psr\Container\ContainerInterface $container) {
    return new UserProfile($container->get('user_name'), $container->get('user_age'));
});

$container->set(User::class, function ($container) {
    return new User($container->get(UserProfile::class));
});

$container->get(User::class);

// Or with default values (`John` and `25`)

$container->set(User::class, User::class);
$container->get(User::class);
```