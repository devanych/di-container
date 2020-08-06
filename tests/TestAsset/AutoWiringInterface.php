<?php

declare(strict_types=1);

namespace Devanych\Tests\Di\TestAsset;

interface AutoWiringInterface
{
    /**
     * @return DummyData
     */
    public function getDummyData(): DummyData;

    /**
     * @return array
     */
    public function getArray(): array;

    /**
     * @return int
     */
    public function getInt(): int;

    /**
     * @return string
     */
    public function getString(): string;
}
