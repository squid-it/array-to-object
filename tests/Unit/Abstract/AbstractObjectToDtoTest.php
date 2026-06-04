<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Unit\Abstract;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use SquidIT\Hydrator\ArrayToObject;
use SquidIT\Hydrator\Class\ClassInfoGenerator;
use SquidIT\Hydrator\Tests\Fixtures\Abstract\BenchmarkCarDto;
use SquidIT\Hydrator\Tests\Fixtures\Abstract\BenchmarkCarDtoFactory;
use SquidIT\Hydrator\Tests\Fixtures\Abstract\BenchmarkDtoState;
use SquidIT\Hydrator\Tests\Fixtures\Abstract\BenchmarkEmployeeDto;
use SquidIT\Hydrator\Tests\Fixtures\Abstract\ExampleDateTimeCollectionDto;
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

    /**
     * @throws Throwable
     */
    public function testToArrayFlattensNestedDtoIntoNestedArraySucceeds(): void
    {
        $benchmarkCarDto = (new BenchmarkCarDtoFactory())->create();

        $arrayData = $benchmarkCarDto->toArray();

        self::assertSame(
            [
                'addressLine1' => 'Beautiful Street 123',
                'addressLine2' => 'Apartment 1234',
                'city'         => 'Rotterdam',
                'employeeList' => [
                    [
                        'employeeName' => 'cecil',
                        'state'        => 'ready',
                    ],
                    [
                        'employeeName' => 'melvin',
                        'state'        => 'ready',
                    ],
                    [
                        'employeeName' => 'sara',
                        'state'        => 'archived',
                    ],
                ],
                'foundedAt' => '2012-04-03T12:13:14.123456',
                'state'     => 'ready',
            ],
            $arrayData['manufacturer'],
        );
    }

    /**
     * @throws Throwable
     */
    public function testToArrayFlattensDtoListPropertyElementWiseSucceeds(): void
    {
        $benchmarkCarDto = (new BenchmarkCarDtoFactory())->create();

        $arrayData = $benchmarkCarDto->toArray();

        self::assertSame(
            [
                [
                    'speedRangeMinRpm' => 200,
                    'speedRangeMaxRpm' => 2160,
                    'isWaterCooled'    => true,
                    'speedCategory'    => 'fast',
                ],
                [
                    'speedRangeMinRpm' => 100,
                    'speedRangeMaxRpm' => 2200,
                    'isWaterCooled'    => false,
                    'speedCategory'    => 'slow',
                ],
                [
                    'speedRangeMinRpm' => 500,
                    'speedRangeMaxRpm' => 3200,
                    'isWaterCooled'    => true,
                    'speedCategory'    => 'fast',
                ],
            ],
            $arrayData['interCoolerList'],
        );
    }

    /**
     * @throws Throwable
     */
    public function testToArrayFormatsDirectDateTimeInterfaceSucceeds(): void
    {
        $exampleDto = new ExampleDto(
            'example',
            new DateTimeImmutable('2026-05-18 12:34:56.123456'),
            ExampleDtoState::Ready,
            null,
            'private-token',
        );

        self::assertSame(
            '2026-05-18T12:34:56.123456',
            $exampleDto->toArray()['createdAt'],
        );
    }

    /**
     * @throws Throwable
     */
    public function testToArrayFormatsDateTimeInterfaceInsideArraySucceeds(): void
    {
        $exampleDateTimeCollectionDto = new ExampleDateTimeCollectionDto(
            [
                new DateTimeImmutable('2026-05-18 12:34:56.123456'),
                'publishedAt' => new DateTimeImmutable('2027-06-19 13:45:57.654321'),
            ],
        );

        self::assertSame(
            [
                'dateList' => [
                    '2026-05-18T12:34:56.123456',
                    'publishedAt' => '2027-06-19T13:45:57.654321',
                ],
            ],
            $exampleDateTimeCollectionDto->toArray(),
        );
    }

    /**
     * @throws Throwable
     */
    public function testToArrayNormalizesBackedEnumAtEveryNestingLevelSucceeds(): void
    {
        $benchmarkCarDto = (new BenchmarkCarDtoFactory())->create();

        $arrayData = $benchmarkCarDto->toArray();

        self::assertSame('ready', $arrayData['state']);
        self::assertSame('ready', $arrayData['manufacturer']['state']);
        self::assertSame('archived', $arrayData['manufacturer']['employeeList'][2]['state']);
        self::assertSame('slow', $arrayData['interCoolerList'][1]['speedCategory']);
    }

    /**
     * @throws Throwable
     */
    public function testToArrayMatchesJsonEncodeDecodeForRepresentativeDtoSucceeds(): void
    {
        $benchmarkCarDto = (new BenchmarkCarDtoFactory())->create();

        $jsonArrayData = json_decode(
            json_encode($benchmarkCarDto, JSON_THROW_ON_ERROR),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        self::assertSame($jsonArrayData, $benchmarkCarDto->toArray());
    }

    /**
     * @throws Throwable
     */
    public function testToArrayRoundTripsThroughArrayHydratorSucceeds(): void
    {
        $benchmarkCarDto = (new BenchmarkCarDtoFactory())->create();
        $arrayToObject   = new ArrayToObject(new ClassInfoGenerator());

        /** @var BenchmarkCarDto $hydratedBenchmarkCarDto */
        $hydratedBenchmarkCarDto = $arrayToObject->hydrate($benchmarkCarDto->toArray(), BenchmarkCarDto::class);

        self::assertSame($benchmarkCarDto->toArray(), $hydratedBenchmarkCarDto->toArray());
        self::assertSame($benchmarkCarDto->getInternalToken(), $hydratedBenchmarkCarDto->getInternalToken());
    }

    /**
     * @throws Throwable
     */
    public function testToArrayKeepsIndependentPropertyNameListsPerDtoClassSucceeds(): void
    {
        $exampleDto = new ExampleDto(
            'example',
            new DateTimeImmutable('2026-05-18 12:34:56.123456'),
            ExampleDtoState::Ready,
            null,
            'private-token',
        );
        $benchmarkEmployeeDto = new BenchmarkEmployeeDto('cecil', BenchmarkDtoState::Ready);

        $exampleData = $exampleDto->toArray();

        self::assertSame($exampleData, $exampleDto->toArray());
        self::assertSame(
            [
                'name'        => 'example',
                'createdAt'   => '2026-05-18T12:34:56.123456',
                'state'       => 'ready',
                'description' => null,
            ],
            $exampleData,
        );
        self::assertSame(
            [
                'employeeName' => 'cecil',
                'state'        => 'ready',
            ],
            $benchmarkEmployeeDto->toArray(),
        );
    }
}
