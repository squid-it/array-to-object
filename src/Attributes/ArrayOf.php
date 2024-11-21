<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
readonly class ArrayOf
{
    public function __construct(
        public string $className,
    ) {}
}
