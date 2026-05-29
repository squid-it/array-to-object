<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Fixtures\Abstract;

use SquidIT\Hydrator\Abstract\AbstractObjectToDto;

final class BenchmarkEmployeeDto extends AbstractObjectToDto
{
    public function __construct(
        public string $employeeName,
        public BenchmarkDtoState $state,
    ) {}
}
