<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Property;

readonly class PropertyDefault
{
    public function __construct(
        public bool $hasDefaultValue,
        public mixed $defaultValue,
    ) {}
}
