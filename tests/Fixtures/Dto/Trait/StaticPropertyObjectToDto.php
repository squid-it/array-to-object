<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Fixtures\Dto\Trait;

use SquidIT\Hydrator\Dto\Interface\ObjectToDtoInterface;
use SquidIT\Hydrator\Dto\Trait\ObjectToDtoTrait;

final class StaticPropertyObjectToDto implements ObjectToDtoInterface
{
    use ObjectToDtoTrait;

    public static string $publicStaticName = 'public-static';

    protected static string $protectedStaticName = 'protected-static';

    public function __construct(
        public string $name,
    ) {}
}
