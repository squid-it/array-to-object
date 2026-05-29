<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Fixtures\Abstract;

use DateTimeInterface;
use SquidIT\Hydrator\Abstract\AbstractObjectToDto;

final class ExampleDateTimeCollectionDto extends AbstractObjectToDto
{
    /**
     * @param array<int|string, DateTimeInterface> $dateList
     */
    public function __construct(
        public array $dateList,
    ) {}
}
