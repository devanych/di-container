<?php

declare(strict_types=1);

namespace Devanych\Tests\Di\TestAsset;

class DummyName
{
    /**
     * @var string
     */
    private $name;

    /**
     * {@inheritDoc}
     */
    public function __construct(string $name = 'Test Name')
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function get(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function set(string $name): void
    {
        $this->name = $name;
    }
}
