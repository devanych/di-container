<?php

declare(strict_types=1);

namespace Devanych\Tests\Di\TestAsset;

class AutoWiringScalarNotDefault
{
    /**
     * @var int
     */
    private int $int;

    /**
     * @var string
     */
    private string $string;

    /**
     * @param int $int
     * @param string $string
     */
    public function __construct(int $int, string $string)
    {
        $this->int = $int;
        $this->string = $string;
    }
}
