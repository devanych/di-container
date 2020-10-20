<?php

declare(strict_types=1);

namespace Devanych\Di;

use Psr\Container\ContainerInterface;

interface FactoryInterface
{
    /**
     * Creates a new object using passed container that implements `Psr\Container\ContainerInterface`.
     *
     * A new object is created from previously defined dependencies or by using container autowiring.
     *
     * Example of use:
     *
     * ```php
     * use Devanych\Di\Container;
     * use Devanych\Di\FactoryInterface;
     * use Psr\Container\ContainerInterface;
     *
     * // Example of an ApplicationFactory test class:
     * final class ApplicationFactory implements FactoryInterface
     * {
     *     public function create(ContainerInterface $container): Application
     *     {
     *         return new Application(
     *             $container->get(Router::class),
     *             $container->get(EmitterInterface::class),
     *             $container->get('environment'),
     *         );
     *     }
     * }
     *
     * // Example of setting dependencies:
     * $container = new Container([
     *     'environment' => 'development',
     *     Application::class => ApplicationFactory::class,
     *     Router::class => fn() => new RouterFactory(),
     *     EmitterInterface::class => new EmitterFactory(),
     * ]);
     *
     * // Creating an Application instance:
     * $application = $container->get(Application::class);
     * ```
     *
     * @param ContainerInterface $container
     * @return object
     */
    public function create(ContainerInterface $container): object;
}
