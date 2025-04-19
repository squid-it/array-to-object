<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Unit\ExampleObjects\Cinema;

readonly class Title
{
    public const DEFAULT_MOVIE_TITLE = 'unknown';

    public function __construct(
        public string $name = self::DEFAULT_MOVIE_TITLE,
    ) {}
}
