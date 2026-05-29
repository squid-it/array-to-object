<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Fixtures\Abstract;

use DateTimeImmutable;
use SquidIT\Hydrator\Abstract\AbstractObjectToDto;
use SquidIT\Hydrator\Attributes\ArrayOf;

final class BenchmarkCarDto extends AbstractObjectToDto
{
    /**
     * @param array<int, string>                  $passengerList
     * @param array<int, BenchmarkInterCoolerDto> $interCoolerList
     */
    public function __construct(
        public string $color,
        public int $nrOfDoors,
        public float $mileagePerLiter,
        public array $passengerList,
        public BenchmarkManufacturerDto $manufacturer,
        #[ArrayOf(BenchmarkInterCoolerDto::class)]
        public array $interCoolerList,
        protected DateTimeImmutable $countryEntryDate,
        public ?string $extraInfo,
        public BenchmarkDtoState $state,
        private string $internalToken = 'private-token',
    ) {}

    public function getInternalToken(): string
    {
        return $this->internalToken;
    }
}
