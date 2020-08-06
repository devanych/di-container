<?php

declare(strict_types=1);

namespace Devanych\Tests\Di\TestAsset;

use Devanych\Di\FactoryInterface;
use Psr\Container\ContainerInterface;

class AutoWiringFactory implements FactoryInterface
{
    /**
     * @var AutoWiringInterface|null
     */
    private ?AutoWiringInterface $autoWiring;

    /**
     * @param AutoWiringInterface|null $autoWiring
     */
    public function __construct(AutoWiringInterface $autoWiring = null)
    {
        $this->autoWiring = $autoWiring;
    }

    /**
     * {@inheritDoc
     */
    public function create(ContainerInterface $container): AutoWiringInterface
    {
        return $this->autoWiring ?? $container->get(AutoWiring::class);
    }
}
