<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Fixtures\Abstract;

enum BenchmarkSpeedCategory: string
{
    case Fast = 'fast';
    case Slow = 'slow';
}
