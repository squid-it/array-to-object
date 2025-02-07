<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Complete;

use DateTimeImmutable;
use SquidIT\Hydrator\Attributes\ArrayOf;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Parts\InterCooler;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Manufacturer\Honda;

readonly class CarSmall
{
    /**
     * @param array<int, InterCooler> $interCoolers
     */
    public function __construct(
        public string $color,
        public array $interCoolers = [],
    ) {}
}
