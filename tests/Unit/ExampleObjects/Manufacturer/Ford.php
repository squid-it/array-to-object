<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Unit\ExampleObjects\Manufacturer;

class Ford implements ManufacturerInterface
{
    public function getName(): string
    {
        return 'Ford';
    }
}
