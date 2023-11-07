<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Simple;

use DateTimeImmutable;

class CarWithCreatedDate
{
    public function __construct(
        public string $color,
        public DateTimeImmutable $createdDate,
    ) {
    }
}
