<?php

declare(strict_types=1);

namespace Devanych\Di;

use Psr\Container\ContainerInterface;

interface FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @return object
     */
    public function create(ContainerInterface $container): object;
}
