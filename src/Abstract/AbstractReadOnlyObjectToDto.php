<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Abstract;

use SquidIT\Hydrator\Dto\Interface\ObjectToDtoInterface;
use SquidIT\Hydrator\Dto\Trait\ObjectToDtoTrait;

abstract readonly class AbstractReadOnlyObjectToDto implements ObjectToDtoInterface
{
    use ObjectToDtoTrait;
}
