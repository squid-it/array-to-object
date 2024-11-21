<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Parts;

readonly class Turbo
{
    public function __construct(
        public int $speedRangeMinRpm,
        public int $speedRangeMaxRpm,
        public bool $isWaterCooled,
    ) {}
}
