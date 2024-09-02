<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Parts;

use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Speed;

readonly class InterCooler
{
    public function __construct(
        public int $speedRangeMinRpm,
        public int $speedRangeMaxRpm,
        public bool $isWaterCooled,
        public Speed $speedCategory,
    ) {}
}
