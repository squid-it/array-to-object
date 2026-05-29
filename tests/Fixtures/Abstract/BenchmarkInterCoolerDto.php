<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Fixtures\Abstract;

use SquidIT\Hydrator\Abstract\AbstractObjectToDto;

final class BenchmarkInterCoolerDto extends AbstractObjectToDto
{
    public function __construct(
        public int $speedRangeMinRpm,
        public int $speedRangeMaxRpm,
        public bool $isWaterCooled,
        public BenchmarkSpeedCategory $speedCategory,
    ) {}
}
