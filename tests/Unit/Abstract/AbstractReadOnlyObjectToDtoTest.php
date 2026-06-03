<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Unit\Abstract;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use SquidIT\Hydrator\Tests\Fixtures\Abstract\ExampleDto;
use SquidIT\Hydrator\Tests\Fixtures\Abstract\ExampleDtoState;
use SquidIT\Hydrator\Tests\Fixtures\Abstract\ExampleNestedReadOnlyDto;
use SquidIT\Hydrator\Tests\Fixtures\Abstract\ExampleReadOnlyDto;
use SquidIT\Hydrator\Tests\Fixtures\Abstract\ExampleReadOnlyNestedDto;
use Throwable;

class AbstractReadOnlyObjectToDtoTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function testToArraySupportsReadOnlyDtoPropertiesSucceeds(): void
    {
        $exampleReadOnlyDto = new ExampleReadOnlyDto(
            'example',
            new DateTimeImmutable('2026-05-18 12:34:56.123456'),
            ExampleDtoState::Ready,
            'readonly-token',
        );

        self::assertSame('readonly-token', $exampleReadOnlyDto->getInternalToken());
        self::assertSame(
            [
                'name'      => 'example',
                'createdAt' => '2026-05-18T12:34:56.123456',
                'state'     => 'ready',
            ],
            $exampleReadOnlyDto->toArray(),
        );
    }

    /**
     * @throws Throwable
     */
    public function testToArrayNormalizesNestedDtoAcrossMutableAndReadOnlyBasesSucceeds(): void
    {
        $exampleDto = new ExampleDto(
            'mutable',
            new DateTimeImmutable('2026-05-18 12:34:56.123456'),
            ExampleDtoState::Ready,
            null,
            'private-token',
        );
        $exampleReadOnlyDto = new ExampleReadOnlyDto(
            'readonly',
            new DateTimeImmutable('2027-06-19 13:45:57.654321'),
            ExampleDtoState::Ready,
            'readonly-token',
        );

        self::assertSame(
            [
                'exampleReadOnlyDto' => [
                    'name'      => 'readonly',
                    'createdAt' => '2027-06-19T13:45:57.654321',
                    'state'     => 'ready',
                ],
            ],
            (new ExampleNestedReadOnlyDto($exampleReadOnlyDto))->toArray(),
        );
        self::assertSame(
            [
                'exampleDto' => [
                    'name'        => 'mutable',
                    'createdAt'   => '2026-05-18T12:34:56.123456',
                    'state'       => 'ready',
                    'description' => null,
                ],
            ],
            (new ExampleReadOnlyNestedDto($exampleDto))->toArray(),
        );
    }
}
