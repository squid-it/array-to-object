<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car;

enum Speed: string
{
    case FAST = 'fast';
    case SLOW = 'slow';
}
