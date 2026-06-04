<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Unit\Dto\Trait;

use PHPUnit\Framework\TestCase;
use SquidIT\Hydrator\Tests\Fixtures\Dto\Trait\StaticPropertyObjectToDto;
use Throwable;

class ObjectToDtoTraitTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function testToArraySkipsStaticPropertiesSucceeds(): void
    {
        $staticPropertyObjectToDto = new StaticPropertyObjectToDto('instance-name');

        self::assertSame(
            [
                'name' => 'instance-name',
            ],
            $staticPropertyObjectToDto->toArray(),
        );
    }
}
