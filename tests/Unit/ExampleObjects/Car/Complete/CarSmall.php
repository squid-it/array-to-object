<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Complete;

use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Parts\InterCooler;

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
