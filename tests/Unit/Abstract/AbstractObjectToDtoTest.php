<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Unit\Abstract;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use SquidIT\Hydrator\Tests\Fixtures\Abstract\ExampleDto;
use SquidIT\Hydrator\Tests\Fixtures\Abstract\ExampleDtoState;
use Throwable;

class AbstractObjectToDtoTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function testJsonSerializeReturnsJsonReadyDtoDataSucceeds(): void
    {
        $exampleDto = new ExampleDto(
            'example',
            new DateTimeImmutable('2026-05-18 12:34:56.123456'),
            ExampleDtoState::Ready,
            null,
            'private-token',
        );

        $serializedData = $exampleDto->jsonSerialize();

        self::assertSame('private-token', $exampleDto->getInternalToken());
        self::assertSame(
            [
                'name'        => 'example',
                'createdAt'   => '2026-05-18T12:34:56.123456',
                'state'       => 'ready',
                'description' => null,
            ],
            $serializedData,
        );
        self::assertArrayNotHasKey('internalToken', $serializedData);
    }
}
