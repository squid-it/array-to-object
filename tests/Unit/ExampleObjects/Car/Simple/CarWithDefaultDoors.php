<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Simple;

use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Manufacturer\Ford;

readonly class CarWithDefaultDoors
{
    /**
     * @param string[] $passengerList
     */
    public function __construct(
        public string $color,
        public float $mileagePerLiter,
        public bool $isInsured,
        public array $passengerList,
        public Ford $manufacturer,
        public ?string $extraInfo,
        public int $nrOfDoors = 3,
    ) {
    }
}
