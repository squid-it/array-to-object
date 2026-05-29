<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Fixtures\Abstract;

enum BenchmarkDtoState: string
{
    case Archived = 'archived';
    case Ready    = 'ready';
}
