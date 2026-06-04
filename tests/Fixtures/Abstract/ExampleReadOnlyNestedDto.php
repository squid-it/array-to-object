<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Fixtures\Abstract;

use SquidIT\Hydrator\Abstract\AbstractReadOnlyObjectToDto;

final readonly class ExampleReadOnlyNestedDto extends AbstractReadOnlyObjectToDto
{
    public function __construct(
        public ExampleDto $exampleDto,
    ) {}
}
