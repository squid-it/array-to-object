<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Simple;

use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Speed;

class CarWithEnumProperty
{
    public function __construct(
        public string $color,
        public Speed $speed,
    ) {
    }
}
