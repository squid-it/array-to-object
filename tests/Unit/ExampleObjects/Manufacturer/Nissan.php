<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Unit\ExampleObjects\Manufacturer;

class Nissan implements ManufacturerInterface
{
    private const NAME = 'Nissan';

    /**
     * @param string[] $employeeList
     */
    public function __construct(
        public readonly string $addressLine1,
        public readonly string $addressLine2,
        public readonly string $city,
        public readonly array $employeeList,
    ) {
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
