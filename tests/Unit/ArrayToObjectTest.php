<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Unit;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use SquidIT\Hydrator\ArrayToObject;
use SquidIT\Hydrator\Class\ClassInfoGenerator;
use SquidIT\Hydrator\Exceptions\AmbiguousTypeException;
use SquidIT\Hydrator\Exceptions\MissingPropertyValueException;
use SquidIT\Hydrator\Exceptions\ValidationFailureException;
use SquidIT\Hydrator\Tests\Unit\ExampleArrays\CarData;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Complete\CarComplete;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Complete\CarCompleteWithNewInConstructor;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Complete\CarWithCustomEngine;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Parts\InterCooler;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Simple\CarWithConstructor;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Simple\CarWithDefaultDoors;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Simple\CarWithDefaultDoorsInNonPromotedProperty;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Manufacturer\Employee;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Manufacturer\Ford;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Manufacturer\Honda;
use Throwable;

class ArrayToObjectTest extends TestCase
{
    private ArrayToObject $arrayToObject;

    protected function setUp(): void
    {
        $classInfoGenerator  = new ClassInfoGenerator();
        $this->arrayToObject = new ArrayToObject($classInfoGenerator);
    }

    /**
     * @throws Throwable
     */
    public function testHydrateClassWithConstructorReturnsObjectWithDefaultPhpTypes(): void
    {
        $data = [
            'color'           => 'red',
            'nrOfDoors'       => 4,
            'mileagePerLiter' => 26.1,
            'isInsured'       => true,
            'passengerList'   => ['cecil', 'melvin'],
            'manufacturer'    => new Ford(),
            'extraInfo'       => null,
        ];

        /** @var CarWithConstructor $simpleCar */
        $simpleCar = $this->arrayToObject->hydrate($data, CarWithConstructor::class);

        foreach ($data as $propertyName => $propertyValue) {
            self::assertSame($propertyValue, $simpleCar->{$propertyName});
        }
    }

    /**
     * @throws Throwable
     */
    public function testHydrateClassWithMissingPropertyDataThrowsException(): void
    {
        $exceptionMsg = sprintf(
            'Could not hydrate object: "%s", no property data provided for: "%s"',
            (new ReflectionClass(CarWithConstructor::class))->getShortName(),
            'nrOfDoors'
        );
        $data = [
            'color'           => 'red',
            'mileagePerLiter' => 26.1,
            'isInsured'       => true,
            'passengerList'   => ['cecil', 'melvin'],
            'manufacturer'    => new Ford(),
            'extraInfo'       => null,
        ];

        $this->expectException(MissingPropertyValueException::class);
        $this->expectExceptionMessage($exceptionMsg);

        $this->arrayToObject->hydrate($data, CarWithConstructor::class);
    }

    /**
     * @throws Throwable
     */
    public function testHydrateClassWithMissingPropertyDataThrowsUserFriendlyExceptionMessage(): void
    {
        $exceptionMsg = sprintf(
            'Path: %s - no data supplied for required property',
            'nrOfDoors'
        );
        $data = [
            'color'           => 'red',
            'mileagePerLiter' => 26.1,
            'isInsured'       => true,
            'passengerList'   => ['cecil', 'melvin'],
            'manufacturer'    => new Ford(),
            'extraInfo'       => null,
        ];

        $this->expectException(MissingPropertyValueException::class);
        $this->expectExceptionMessage($exceptionMsg);

        $arrayToObject = new ArrayToObject(new ClassInfoGenerator(), true);
        $arrayToObject->hydrate($data, CarWithConstructor::class);
    }

    /**
     * @throws Throwable
     */
    public function testHydrateClassWithMissingPropertyDataUsesDefaultFromConstructor(): void
    {
        $data = [
            'color'           => 'red',
            'mileagePerLiter' => 26.1,
            'isInsured'       => true,
            'passengerList'   => ['cecil', 'melvin'],
            'manufacturer'    => new Ford(),
            'extraInfo'       => null,
        ];

        /** @var CarWithDefaultDoors $carWithDefaultNrOfDoors */
        $carWithDefaultNrOfDoors = $this->arrayToObject->hydrate($data, CarWithDefaultDoors::class);

        self::assertSame(3, $carWithDefaultNrOfDoors->nrOfDoors);
    }

    /**
     * @throws Throwable
     */
    public function testHydrateClassWithMissingPropertyDataUsesDefaultFromDefaultProperty(): void
    {
        $data = [
            'color'           => 'red',
            'mileagePerLiter' => 26.1,
            'isInsured'       => true,
            'passengerList'   => ['cecil', 'melvin'],
            'manufacturer'    => new Ford(),
            'extraInfo'       => null,
        ];

        /** @var CarWithDefaultDoorsInNonPromotedProperty $carWithDefaultDoorsInNonPromotedProperty */
        $carWithDefaultDoorsInNonPromotedProperty = $this->arrayToObject->hydrate($data, CarWithDefaultDoorsInNonPromotedProperty::class);

        self::assertSame(4, $carWithDefaultDoorsInNonPromotedProperty->nrOfDoors);
    }

    /**
     * @throws Throwable
     */
    public function testHydratingPropertyExpectingArrayOfObjectsAcceptsEmptyArray(): void
    {
        $data = [
            'addressLine1' => 'street 1',
            'addressLine2' => '3011 AA1',
            'city'         => 'Rotterdam',
            'employeeList' => [],
        ];

        /** @var Honda $honda */
        $honda = $this->arrayToObject->hydrate($data, Honda::class);
        self::assertInstanceOf(Honda::class, $honda);
    }

    /**
     * @throws Throwable
     */
    public function testHydratingFullObjectWithNestedElementsSucceeds(): void
    {
        $data = CarData::regularArray();

        /** @var CarComplete $car */
        $car = $this->arrayToObject->hydrate($data, CarComplete::class);

        // basic
        self::assertSame($data['color'], $car->color);
        self::assertSame($data['nrOfDoors'], $car->nrOfDoors);
        self::assertSame($data['mileagePerLiter'], $car->mileagePerLiter);
        self::assertSame($data['passengerList'], $car->passengerList);

        // null value
        self::assertSame($data['extraInfo'], $car->extraInfo);

        // default
        self::assertTrue($car->isInsured);

        // object containing a nested array of objects
        /** @var array<string, array<string, string>|string> $manufacturerData */
        $manufacturerData = $data['manufacturer'];

        /** @var array<int, array<string, string>> $employeeListData */
        $employeeListData = $manufacturerData['employeeList'];

        $manufacturer = $car->manufacturer;
        $employeeList = $car->manufacturer->employeeList;

        self::assertInstanceOf(Honda::class, $manufacturer);
        self::assertSame($manufacturerData['addressLine1'], $manufacturer->addressLine1);
        self::assertSame($manufacturerData['addressLine2'], $manufacturer->addressLine2);
        self::assertSame($manufacturerData['city'], $manufacturer->city);

        self::assertContainsOnlyInstancesOf(Employee::class, $employeeList);
        self::assertSame($employeeListData[0]['employeeName'], $employeeList[0]->employeeName);
        self::assertSame($employeeListData[1]['employeeName'], $employeeList[1]->employeeName);

        // Cast to date
        self::assertInstanceOf(DateTimeImmutable::class, $car->countryEntryDate);
        self::assertSame($data['countryEntryDate'], $car->countryEntryDate->format('Y-m-d H:i:s'));

        // Cast to enum
        /** @var array<int, array<string, bool|int|string>> $interCoolerData */
        $interCoolerData = $data['interCoolers'];
        $intercoolerList = $car->interCoolers;

        self::assertContainsOnlyInstancesOf(InterCooler::class, $intercoolerList);
        self::assertInstanceOf(DateTimeImmutable::class, $car->countryEntryDate);
        self::assertSame($interCoolerData[0]['speedCategory'], $intercoolerList[0]->speedCategory->value);
        self::assertSame($interCoolerData[1]['speedCategory'], $intercoolerList[1]->speedCategory->value);
    }

    /**
     * @throws Throwable
     */
    public function testHydratingRetainsObjectIndexKeys(): void
    {
        $key1         = 4;
        $key2         = 33;
        $key1Original = 0;
        $key2Original = 1;
        $data         = CarData::regularArray();

        // set specific array keys (reuse existing data)
        $data['interCoolers'][$key1] = $data['interCoolers'][$key1Original]; /** @phpstan-ignore-line */
        $data['interCoolers'][$key2] = $data['interCoolers'][$key2Original];
        unset($data['interCoolers'][$key1Original], $data['interCoolers'][$key2Original]);

        /** @var CarComplete $car */
        $car = $this->arrayToObject->hydrate($data, CarComplete::class);

        self::assertArrayHasKey($key1, $car->interCoolers);
        self::assertArrayHasKey($key2, $car->interCoolers);
        self::assertArrayNotHasKey($key1Original, $car->interCoolers);
        self::assertArrayNotHasKey($key2Original, $car->interCoolers);
    }

    /**
     * @throws Throwable
     */
    public function testHydratingFullObjectWithInitializerInConstructorAsDefaultValueSucceeds(): void
    {
        $currentTime = new DateTimeImmutable();

        $data = CarData::regularArray();
        unset($data['countryEntryDate']);

        /** @var CarCompleteWithNewInConstructor $car */
        $car = $this->arrayToObject->hydrate($data, CarCompleteWithNewInConstructor::class);

        self::assertInstanceOf(DateTimeImmutable::class, $car->countryEntryDate);
        self::assertTrue(
            $currentTime->modify('-5 seconds') < $car->countryEntryDate
            && $currentTime->modify('+5 seconds') > $car->countryEntryDate
        );
    }

    // /////////
    // Multi //
    // /////////

    /**
     * @throws Throwable
     */
    public function testHydratingArrayOfObjectsSucceeds(): void
    {
        $specificArrayKey = 36;
        $data             = [
            CarData::regularArray(),
            $specificArrayKey => CarData::regularArray(),
            CarData::regularArray(),
        ];

        /** @var array<int, CarComplete> $cars */
        $cars = $this->arrayToObject->hydrateMulti($data, CarComplete::class);

        self::assertContainsOnlyInstancesOf(CarComplete::class, $cars);
        self::assertArrayHasKey($specificArrayKey, $cars);
    }

    /**
     * @throws Throwable
     */
    public function testHydratingArrayOfObjectsThrowsAmbiguousTypeExceptionOnInvalidArrayOfObjectKey(): void
    {
        $data = ['test' => CarData::regularArray()];

        $this->expectException(AmbiguousTypeException::class);

        /* @phpstan-ignore-next-line */
        $this->arrayToObject->hydrateMulti($data, CarComplete::class);
    }

    /**
     * @throws Throwable
     */
    public function testHydratingArrayOfObjectsThrowsAmbiguousTypeExceptionOnInvalidArrayOfObjectArray(): void
    {
        $data = [12 => 'bert'];

        $this->expectException(AmbiguousTypeException::class);

        /* @phpstan-ignore-next-line */
        $this->arrayToObject->hydrateMulti($data, CarComplete::class);
    }

    /**
     * @throws Throwable
     */
    public function testHydratingObjectThrowsValidationFailureExceptionWhenObjectExtendingObjectValidatorFailsCheck(): void
    {
        $displacementValueOk = 600;
        $dataOke             = ['engineDisplacementInCc' => $displacementValueOk];
        $dataBad             = ['engineDisplacementInCc' => 10000];

        $CarWithCustomEngine = $this->arrayToObject->hydrate($dataOke, CarWithCustomEngine::class);
        self::assertSame($displacementValueOk, $CarWithCustomEngine->engineDisplacementInCc);

        $this->expectException(ValidationFailureException::class);
        $this->expectExceptionMessage('Invalid value received for property: engineDisplacementInCc, value needs to be between 600 and 8000');

        $this->arrayToObject->hydrate($dataBad, CarWithCustomEngine::class);
    }
}
