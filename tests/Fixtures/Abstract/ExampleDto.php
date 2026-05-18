<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Fixtures\Abstract;

use DateTimeImmutable;
use SquidIT\Hydrator\Abstract\AbstractObjectToDto;

final class ExampleDto extends AbstractObjectToDto
{
    public function __construct(
        public string $name,
        protected DateTimeImmutable $createdAt,
        public ExampleDtoState $state,
        public ?string $description,
        private string $internalToken,
    ) {}

    public function getInternalToken(): string
    {
        return $this->internalToken;
    }
}
