<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Simple;

use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Manufacturer\Ford;

class CarWithUnionType
{
    /**
     * @param string[] $passengerList
     */
    public function __construct(
        public string $color,
        public int $nrOfDoors,
        public float $mileagePerLiter,
        public bool $isInsured,
        public array $passengerList,
        public Ford $manufacturer,
        public float|int $extraInfo,
    ) {}
}
