<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Fixtures\Abstract;

use SquidIT\Hydrator\Abstract\AbstractObjectToDto;

final class ExampleNestedReadOnlyDto extends AbstractObjectToDto
{
    public function __construct(
        public ExampleReadOnlyDto $exampleReadOnlyDto,
    ) {}
}
