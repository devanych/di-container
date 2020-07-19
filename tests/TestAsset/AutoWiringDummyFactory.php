<?php

declare(strict_types=1);

namespace Devanych\Tests\Di\TestAsset;

use Devanych\Di\FactoryInterface;
use Psr\Container\ContainerInterface;

class AutoWiringDummyFactory implements FactoryInterface
{
    /**
     * {@inheritDoc
     */
    public function create(ContainerInterface $container): object
    {
        return $container->get(DummyFactory::class);
    }
}
