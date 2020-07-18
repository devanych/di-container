<?php

declare(strict_types=1);

namespace Devanych\Tests\Di\TestAsset;

class DummyData
{
    /**
     * @var DummyName
     */
    private $name;

    /**
     * @var mixed
     */
    private $time;

    /**
     * {@inheritDoc}
     */
    public function __construct(DummyName $name, $time = null)
    {
        $this->name = $name;
        $this->time = $time;
    }

    /**
     * @return DummyName
     */
    public function getName(): DummyName
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getTime()
    {
        return $this->time;
    }
}
