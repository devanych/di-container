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
     * // Example of an ApplicationFactory test class:
     * final class ApplicationFactory implements \Devanych\Di\FactoryInterface
     * {
     *     public ?string $environment;
     *
     *     public function __construct(string $environment = null)
     *     {
     *         $this->environment = $environment;
     *     }
     *
     *     public function create(ContainerInterface $container): object
     *     {
     *         return new Application(
     *             $container->get(Router::class),
     *             $container->get(EmitterInterface::class),
     *             $this->environment ?? $container->get('environment'),
     *         );
     *     }
     * }
     *
     * // Example of setting dependencies:
     * $container = new \Devanych\Di\Container([
     *     'environment' => 'development',
     *     Application::class => ApplicationFactory::class,
     *     Router::class => RouterFactory::class,
     *     EmitterInterface::class => EmitterFactory::class,
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
