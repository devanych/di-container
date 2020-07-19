<?php

declare(strict_types=1);

namespace Devanych\Tests\Di\TestAsset;

class AutoWiring
{
    /**
     * @var DummyData
     */
    private DummyData $dummyData;

    /**
     * @var array
     */
    private array $array;

    /**
     * @var int
     */
    private int $int;

    /**
     * @var string
     */
    private string $string;

    /**
     * @param DummyData $dummyData
     * @param array $array
     * @param int $int
     * @param string $string
     */
    public function __construct(DummyData $dummyData, array $array, int $int = 100, string $string = 'string')
    {
        $this->dummyData = $dummyData;
        $this->array = $array;
        $this->int = $int;
        $this->string = $string;
    }

    /**
     * @return DummyData
     */
    public function getDummyData(): DummyData
    {
        return $this->dummyData;
    }

    /**
     * @return array
     */
    public function getArray(): array
    {
        return $this->array;
    }

    /**
     * @return int
     */
    public function getInt(): int
    {
        return $this->int;
    }

    /**
     * @return string
     */
    public function getString(): string
    {
        return $this->string;
    }
}
