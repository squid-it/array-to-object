<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Complete;

use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Parts\InterCooler;

readonly class CarSmall
{
    public string $color;

    /** @var InterCooler[] */
    public array $interCoolers;

    /**
     * @param array<int, InterCooler> $interCoolers
     */
    public function __construct(
        string $color,
        array $interCoolers = [],
    ) {
        $this->color        = $color;
        $this->interCoolers = $interCoolers;
    }
}
