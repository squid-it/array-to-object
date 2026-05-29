<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Fixtures\Abstract;

use DateTimeImmutable;
use SquidIT\Hydrator\Abstract\AbstractObjectToDto;
use SquidIT\Hydrator\Attributes\ArrayOf;

final class BenchmarkManufacturerDto extends AbstractObjectToDto
{
    /**
     * @param array<int, BenchmarkEmployeeDto> $employeeList
     */
    public function __construct(
        public string $addressLine1,
        protected string $addressLine2,
        public string $city,
        #[ArrayOf(BenchmarkEmployeeDto::class)]
        public array $employeeList,
        protected DateTimeImmutable $foundedAt,
        public BenchmarkDtoState $state,
    ) {}
}
