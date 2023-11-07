<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Simple;

use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Manufacturer\ManufacturerInterface;

class CarWithOutConstructor
{
    protected float $mileagePerLiter;

    private string $color;

    private int $nrOfDoors;

    private bool $isInsured;

    /** @var string[] */
    private array $passengerList;

    private ManufacturerInterface $manufacturer;

    private ?string $extraInfo;

    public function getMileagePerLiter(): float
    {
        return $this->mileagePerLiter;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function getNrOfDoors(): int
    {
        return $this->nrOfDoors;
    }

    public function isInsured(): bool
    {
        return $this->isInsured;
    }

    /**
     * @return string[]
     */
    public function getPassengerList(): array
    {
        return $this->passengerList;
    }

    public function getManufacturer(): ManufacturerInterface
    {
        return $this->manufacturer;
    }

    public function getExtraInfo(): ?string
    {
        return $this->extraInfo;
    }
}
