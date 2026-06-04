<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Dto\Interface;

use JsonSerializable;

interface ObjectToDtoInterface extends JsonSerializable
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
