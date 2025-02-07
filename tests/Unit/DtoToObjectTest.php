<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use SquidIT\Hydrator\Class\ClassInfoGenerator;
use SquidIT\Hydrator\DtoToObject;
use SquidIT\Hydrator\Exceptions\AmbiguousTypeException;
use SquidIT\Hydrator\Tests\Unit\ExampleArrays\CarData;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Complete\CarComplete;

class DtoToObjectTest extends TestCase
{
    /**
     * @throws ReflectionException
     * @throws AmbiguousTypeException
     * @throws JsonException
     */
    public function testHydratingFullObjectWithNestedElementsSucceeds(): void
    {
        $data               = CarData::regularObject();
        $classInfoGenerator = new ClassInfoGenerator();
        $hydrator           = new DtoToObject($classInfoGenerator);

        /** @var CarComplete $car */
        $car = $hydrator->hydrate($data, CarComplete::class);

        self::assertIsObject($car);
    }

    /**
     * @throws ReflectionException
     * @throws AmbiguousTypeException
     * @throws JsonException
     */
    public function testHydratingFullObjectsWithNestedElementsSucceeds(): void
    {
        $data = [
            CarData::regularObject(),
            CarData::regularObject(),
            CarData::regularObject(),
        ];
        $classInfoGenerator = new ClassInfoGenerator();
        $hydrator           = new DtoToObject($classInfoGenerator);

        /** @var array<int, CarComplete> $cars */
        $cars = $hydrator->hydrateMulti($data, CarComplete::class);

        self::assertContainsOnlyInstancesOf(CarComplete::class, $cars);
    }
}
