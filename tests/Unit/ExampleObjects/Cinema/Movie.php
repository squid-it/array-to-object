<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Unit\ExampleObjects\Cinema;

use DateTimeImmutable;

readonly class Movie
{
    public function __construct(
        public DateTimeImmutable $releaseDate,
        public Title $title = new Title(),
    ) {}
}
