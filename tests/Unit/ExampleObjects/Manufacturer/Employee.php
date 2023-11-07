<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Unit\ExampleObjects\Manufacturer;

readonly class Employee
{
    public function __construct(
        public string $employeeName
    ) {
    }
}
