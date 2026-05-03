<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use SquidIT\Hydrator\Class\ClassInfoGenerator;
use SquidIT\Hydrator\DtoToObject;
use SquidIT\Hydrator\Exceptions\AmbiguousTypeException;
use SquidIT\Hydrator\Exceptions\MissingPropertyValueException;
use SquidIT\Hydrator\Tests\Fixtures\DtoToObjectInputChecker;
use SquidIT\Hydrator\Tests\Unit\ExampleArrays\CarData;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Complete\CarComplete;

class DtoToObjectTest extends TestCase
{
    private DtoToObject $dtoToObject;

    protected function setUp(): void
    {
        $this->dtoToObject = new DtoToObject(new ClassInfoGenerator());
    }

    /**
     * @throws ReflectionException
     * @throws AmbiguousTypeException
     * @throws JsonException
     */
    public function testHydratingFullObjectWithNestedElementsSucceeds(): void
    {
        $data = CarData::regularObject();
        /** @var CarComplete $car */
        $car = $this->dtoToObject->hydrate($data, CarComplete::class);

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
        /** @var array<int, CarComplete> $cars */
        $cars = $this->dtoToObject->hydrateMulti($data, CarComplete::class);

        self::assertContainsOnlyInstancesOf(CarComplete::class, $cars);
    }

    /**
     * @throws ReflectionException
     * @throws AmbiguousTypeException
     * @throws JsonException
     */
    public function testHydratingObjectWithMissingPropertyDataThrowsException(): void
    {
        $data = CarData::regularObject();
        unset($data->nrOfDoors);

        $this->expectException(MissingPropertyValueException::class);
        $this->expectExceptionMessage(
            'Could not hydrate object: "SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Complete\CarComplete", supplied object does not contain property: "nrOfDoors" (nrOfDoors)'
        );

        $this->dtoToObject->hydrate($data, CarComplete::class);
    }

    /**
     * @throws ReflectionException
     * @throws AmbiguousTypeException
     * @throws JsonException
     */
    public function testHydratingObjectWithMissingPropertyDataThrowsUserFriendlyExceptionMessage(): void
    {
        $data = CarData::regularObject();
        unset($data->nrOfDoors);

        $this->expectException(MissingPropertyValueException::class);
        $this->expectExceptionMessage('Path: nrOfDoors - no data supplied for required property');

        $dtoToObject = new DtoToObject(new ClassInfoGenerator(), true);
        $dtoToObject->hydrate($data, CarComplete::class);
    }

    /**
     * @throws ReflectionException
     * @throws AmbiguousTypeException
     */
    public function testHydratingArrayOfObjectsThrowsAmbiguousTypeExceptionOnInvalidArrayOfObjectKey(): void
    {
        $data = ['test' => CarData::regularObject()];

        $this->expectException(AmbiguousTypeException::class);
        $this->expectExceptionMessage(
            'Could not hydrate an Array of "SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Complete\CarComplete" input array needs to be an indexed (list) of arrays'
        );

        (new DtoToObjectInputChecker(new ClassInfoGenerator()))->checkArrayOfObjectsInput($data, CarComplete::class);
    }

    /**
     * @throws ReflectionException
     * @throws AmbiguousTypeException
     */
    public function testHydratingArrayOfObjectsThrowsAmbiguousTypeExceptionOnInvalidArrayOfObjectArray(): void
    {
        $data = [12 => ['color' => 'black']];

        $this->expectException(AmbiguousTypeException::class);
        $this->expectExceptionMessage(
            'Could not hydrate an Array of "SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Complete\CarComplete" input array needs to be an indexed (list) of arrays'
        );

        (new DtoToObjectInputChecker(new ClassInfoGenerator()))->checkArrayOfObjectsInput($data, CarComplete::class);
    }
}
