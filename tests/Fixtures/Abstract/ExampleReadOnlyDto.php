<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Fixtures\Abstract;

use DateTimeImmutable;
use SquidIT\Hydrator\Abstract\AbstractReadOnlyObjectToDto;

final readonly class ExampleReadOnlyDto extends AbstractReadOnlyObjectToDto
{
    public function __construct(
        public string $name,
        protected DateTimeImmutable $createdAt,
        public ExampleDtoState $state,
        private string $internalToken,
    ) {}

    public function getInternalToken(): string
    {
        return $this->internalToken;
    }
}
