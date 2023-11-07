<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Nested;

use SquidIT\Hydrator\Attributes\ArrayOf;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Parts\Turbo;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Manufacturer\Nissan;

readonly class CarWithMultipleTurbosWithAttributedTurboArray
{
    /**
     * @param array<int, Turbo> $turbos
     */
    public function __construct(
        public string $color,
        public Nissan $manufacturer,
        #[ArrayOf(Turbo::class)]
        public array $turbos,
    ) {
    }
}
