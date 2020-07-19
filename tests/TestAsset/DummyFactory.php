<?php

declare(strict_types=1);

namespace Devanych\Tests\Di\TestAsset;

use Devanych\Di\FactoryInterface;
use Psr\Container\ContainerInterface;

use function microtime;

class DummyFactory implements FactoryInterface
{
    /**
     * @var mixed|null
     */
    private $time;

    /**
     * @param mixed|null $time
     */
    public function __construct($time = null)
    {
        $this->time = $time ?? microtime(true);
    }

    /**
     * {@inheritDoc
     */
    public function create(ContainerInterface $container): object
    {
        return new DummyData($container->get(DummyName::class), $this->time);
    }

    /**
     * @return mixed|null
     */
    public function getTime()
    {
        return $this->time;
    }
}
