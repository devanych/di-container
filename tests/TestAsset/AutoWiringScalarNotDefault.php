<?php

declare(strict_types=1);

namespace Devanych\Tests\Di\TestAsset;

class AutoWiringScalarNotDefault
{
    /**
     * @var int
     */
    private $int;

    /**
     * @var string
     */
    private $string;

    /**
     * {@inheritDoc}
     */
    public function __construct(int $int, string $string)
    {
        $this->int = $int;
        $this->string = $string;
    }
}
