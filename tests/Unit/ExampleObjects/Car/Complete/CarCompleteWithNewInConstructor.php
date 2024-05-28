<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Complete;

use DateTimeImmutable;
use SquidIT\Hydrator\Attributes\ArrayOf;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Parts\InterCooler;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Manufacturer\Honda;

readonly class CarCompleteWithNewInConstructor
{
    /**
     * @param string[]                $passengerList
     * @param array<int, InterCooler> $interCoolers
     */
    public function __construct(
        public string $color,
        public int $nrOfDoors,
        public float $mileagePerLiter,
        public array $passengerList,
        public Honda $manufacturer,
        #[ArrayOf(InterCooler::class)]
        public array $interCoolers,
        public ?string $extraInfo,
        public DateTimeImmutable $countryEntryDate = new DateTimeImmutable(),
        public bool $isInsured = true,
    ) {
    }
}
