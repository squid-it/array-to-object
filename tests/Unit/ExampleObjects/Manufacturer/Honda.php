<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Unit\ExampleObjects\Manufacturer;

use SquidIT\Hydrator\Attributes\ArrayOf;

class Honda implements ManufacturerInterface
{
    private const NAME = 'Honda';

    /**
     * @param Employee[] $employeeList
     */
    public function __construct(
        public readonly string $addressLine1,
        public readonly string $addressLine2,
        public readonly string $city,
        #[ArrayOf(Employee::class)]
        public readonly array $employeeList,
    ) {}

    public function getName(): string
    {
        return self::NAME;
    }
}
