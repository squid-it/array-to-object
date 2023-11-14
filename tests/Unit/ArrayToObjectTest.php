<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Unit;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionEnum;
use ReflectionException;
use ReflectionNamedType;
use SquidIT\Hydrator\ArrayToObject;
use SquidIT\Hydrator\Class\ClassInfoGenerator;
use SquidIT\Hydrator\Class\ClassProperty;
use SquidIT\Hydrator\Exceptions\AmbiguousTypeException;
use SquidIT\Hydrator\Exceptions\MissingPropertyValueException;
use SquidIT\Hydrator\Exceptions\UnableToCastPropertyValueException;
use SquidIT\Hydrator\Tests\Unit\ExampleArrays\CarArray;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Complete\CarComplete;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Complete\CarCompleteWithNewInConstructor;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Parts\InterCooler;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Simple\CarWithConstructor;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Simple\CarWithCreatedDate;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Simple\CarWithDefaultDoors;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Simple\CarWithDefaultDoorsInNonPromotedProperty;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Simple\CarWithEnumProperty;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Speed;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Manufacturer\Employee;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Manufacturer\Ford;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Manufacturer\Honda;

class ArrayToObjectTest extends TestCase
{
    /**
     * @throws ReflectionException
     * @throws AmbiguousTypeException
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

        $classInfoGenerator = new ClassInfoGenerator();
        $arrayToObject      = new ArrayToObject($classInfoGenerator);

        /** @var CarWithConstructor $simpleCar */
        $simpleCar = $arrayToObject->hydrate($data, CarWithConstructor::class);

        foreach ($data as $propertyName => $propertyValue) {
            self::assertSame($propertyValue, $simpleCar->{$propertyName});
        }
    }

    /**
     * @throws ReflectionException
     * @throws AmbiguousTypeException
     */
    public function testHydrateClassWithMissingPropertyDataThrowsException(): void
    {
        $exceptionMsg = sprintf(
            'Could not hydrate object: "%s", no property data provided for: "%s"',
            CarWithConstructor::class,
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

        $classInfoGenerator = new ClassInfoGenerator();
        $arrayToObject      = new ArrayToObject($classInfoGenerator);

        $arrayToObject->hydrate($data, CarWithConstructor::class);
    }

    /**
     * @throws ReflectionException
     * @throws AmbiguousTypeException
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

        $classInfoGenerator = new ClassInfoGenerator();
        $arrayToObject      = new ArrayToObject($classInfoGenerator);

        /** @var CarWithDefaultDoors $carWithDefaultNrOfDoors */
        $carWithDefaultNrOfDoors = $arrayToObject->hydrate($data, CarWithDefaultDoors::class);

        self::assertSame(3, $carWithDefaultNrOfDoors->nrOfDoors);
    }

    /**
     * @throws ReflectionException
     * @throws AmbiguousTypeException
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

        $classInfoGenerator = new ClassInfoGenerator();
        $arrayToObject      = new ArrayToObject($classInfoGenerator);

        /** @var CarWithDefaultDoorsInNonPromotedProperty $carWithDefaultDoorsInNonPromotedProperty */
        $carWithDefaultDoorsInNonPromotedProperty = $arrayToObject->hydrate($data, CarWithDefaultDoorsInNonPromotedProperty::class);

        self::assertSame(4, $carWithDefaultDoorsInNonPromotedProperty->nrOfDoors);
    }

    /**
     * @throws ReflectionException
     * @throws AmbiguousTypeException
     */
    public function testHydratingFullObjectWithNestedElementsSucceeds(): void
    {
        $data = CarArray::regular();

        $classInfoGenerator = new ClassInfoGenerator();
        $arrayToObject      = new ArrayToObject($classInfoGenerator);

        /** @var CarComplete $car */
        $car = $arrayToObject->hydrate($data, CarComplete::class);

        // basic
        self::assertSame($data['color'], $car->color);
        self::assertSame($data['nrOfDoors'], $car->nrOfDoors);
        self::assertSame($data['mileagePerLiter'], $car->mileagePerLiter);
        self::assertSame($data['passengerList'], $car->passengerList);

        // null value
        self::assertSame($data['extraInfo'], $car->extraInfo);

        // default
        self::assertTrue($car->isInsured);

        // object with nested array of objects
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
     * @throws ReflectionException
     * @throws AmbiguousTypeException
     */
    public function testHydratingFullObjectWithInitializerInConstructorAsDefaultValueSucceeds(): void
    {
        $currentTime = new DateTimeImmutable();

        $data = CarArray::regular();
        unset($data['countryEntryDate']);

        $classInfoGenerator = new ClassInfoGenerator();
        $arrayToObject      = new ArrayToObject($classInfoGenerator);

        /** @var CarCompleteWithNewInConstructor $car */
        $car = $arrayToObject->hydrate($data, CarCompleteWithNewInConstructor::class);

        self::assertInstanceOf(DateTimeImmutable::class, $car->countryEntryDate);
        self::assertTrue(
            $currentTime->modify('-5 seconds') < $car->countryEntryDate
            && $currentTime->modify('+5 seconds') > $car->countryEntryDate
        );
    }

    // ///////////
    // Casting //
    // ///////////

    /**
     * @throws UnableToCastPropertyValueException
     */
    #[DataProvider('castToBoolSucceedsProvider')]
    public function testCastToBoolSucceeds(mixed $inputValue, bool $expected): void
    {
        $reflectionClass    = new ReflectionClass(CarWithDefaultDoors::class);
        $reflectionProperty = $reflectionClass->getProperty('isInsured');

        /** @var ReflectionNamedType $reflectionPropertyType */
        $reflectionPropertyType = $reflectionProperty->getType();

        $classProperty = new ClassProperty(
            $reflectionClass->name,
            $reflectionProperty->getName(),
            false,
            $reflectionPropertyType->getName(),
            false,
            false,
            $reflectionPropertyType->isBuiltin(),
            $reflectionPropertyType->allowsNull(),
            null
        );

        $classInfoGenerator = new ClassInfoGenerator();
        $arrayToObject      = new ArrayToObject($classInfoGenerator);
        $value              = $arrayToObject->castValue($inputValue, $classProperty);

        self::assertSame($expected, $value);
    }

    /**
     * @throws UnableToCastPropertyValueException
     */
    public function testCastToDateTimeImmutableSucceeds(): void
    {
        $dateTimeString     = '2023-01-01 12:00:00.48596';
        $reflectionClass    = new ReflectionClass(CarWithCreatedDate::class);
        $reflectionProperty = $reflectionClass->getProperty('createdDate');

        /** @var ReflectionNamedType $reflectionPropertyType */
        $reflectionPropertyType = $reflectionProperty->getType();

        $classProperty = new ClassProperty(
            $reflectionClass->name,
            $reflectionProperty->getName(),
            false,
            $reflectionPropertyType->getName(),
            false,
            false,
            $reflectionPropertyType->isBuiltin(),
            $reflectionPropertyType->allowsNull(),
            null
        );

        $classInfoGenerator = new ClassInfoGenerator();
        $arrayToObject      = new ArrayToObject($classInfoGenerator);
        $value              = $arrayToObject->castValue($dateTimeString, $classProperty);

        self::assertInstanceOf(DateTimeImmutable::class, $value);
    }

    public function testCastToDateTimeImmutableThrowsUnableToCastPropertyValueExceptionOnInvalidDateTime(): void
    {
        $dateTimeString     = '20-error-23-01-01 12:00:00.48596';
        $reflectionClass    = new ReflectionClass(CarWithCreatedDate::class);
        $reflectionProperty = $reflectionClass->getProperty('createdDate');

        /** @var ReflectionNamedType $reflectionPropertyType */
        $reflectionPropertyType = $reflectionProperty->getType();

        $classProperty = new ClassProperty(
            $reflectionClass->name,
            $reflectionProperty->getName(),
            false,
            $reflectionPropertyType->getName(),
            false,
            false,
            $reflectionPropertyType->isBuiltin(),
            $reflectionPropertyType->allowsNull(),
            null
        );

        $exceptionMsg = sprintf(
            'Unable to cast value: "%s" to %s::%s (%s) - %s',
            $dateTimeString,
            $reflectionClass->name,
            $reflectionProperty->getName(),
            $reflectionPropertyType->getName(),
            'Failed to parse time string (20-error-23-01-01 12:00:00.48596) at position 0 (2): Unexpected character'
        );

        $this->expectException(UnableToCastPropertyValueException::class);
        $this->expectExceptionMessage($exceptionMsg);

        $classInfoGenerator = new ClassInfoGenerator();
        $arrayToObject      = new ArrayToObject($classInfoGenerator);

        $arrayToObject->castValue($dateTimeString, $classProperty);
    }

    /**
     * @throws ReflectionException|UnableToCastPropertyValueException
     */
    public function testCastToEnumSucceeds(): void
    {
        $enumBackedValue    = 'fast';
        $enumExpected       = Speed::FAST;
        $reflectionClass    = new ReflectionClass(CarWithEnumProperty::class);
        $reflectionProperty = $reflectionClass->getProperty('speed');

        /** @var ReflectionNamedType $reflectionPropertyType */
        $reflectionPropertyType = $reflectionProperty->getType();

        $reflectionEnum = new ReflectionEnum($reflectionPropertyType->getName());

        $classProperty = new ClassProperty(
            $reflectionClass->name,
            $reflectionProperty->getName(),
            $reflectionEnum->isEnum() && $reflectionEnum->isBacked(),
            $reflectionPropertyType->getName(),
            false,
            false,
            $reflectionPropertyType->isBuiltin(),
            $reflectionPropertyType->allowsNull(),
            null
        );

        $classInfoGenerator = new ClassInfoGenerator();
        $arrayToObject      = new ArrayToObject($classInfoGenerator);
        $value              = $arrayToObject->castValue($enumBackedValue, $classProperty);

        self::assertSame($enumExpected, $value);
    }

    /**
     * @throws ReflectionException
     */
    public function testCastToEnumThrowsUnableToCastPropertyValueExceptionOnInvalidBackedEnumValue(): void
    {
        $enumBackedValue    = 'normal';
        $reflectionClass    = new ReflectionClass(CarWithEnumProperty::class);
        $reflectionProperty = $reflectionClass->getProperty('speed');

        /** @var ReflectionNamedType $reflectionPropertyType */
        $reflectionPropertyType = $reflectionProperty->getType();

        $reflectionEnum = new ReflectionEnum($reflectionPropertyType->getName());

        $classProperty = new ClassProperty(
            $reflectionClass->name,
            $reflectionProperty->getName(),
            $reflectionEnum->isEnum() && $reflectionEnum->isBacked(),
            $reflectionPropertyType->getName(),
            false,
            false,
            $reflectionPropertyType->isBuiltin(),
            $reflectionPropertyType->allowsNull(),
            null
        );

        $exceptionMsg = sprintf(
            'Unable to cast value: "%s" to %s::%s (%s - Backed Enum) - %s',
            $enumBackedValue,
            $reflectionClass->name,
            $reflectionProperty->getName(),
            $reflectionPropertyType->getName(),
            '"normal" is not a valid backing value for enum SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Speed'
        );

        $this->expectException(UnableToCastPropertyValueException::class);
        $this->expectExceptionMessage($exceptionMsg);

        $classInfoGenerator = new ClassInfoGenerator();
        $arrayToObject      = new ArrayToObject($classInfoGenerator);
        $arrayToObject->castValue($enumBackedValue, $classProperty);
    }

    // /////////
    // Multi //
    // /////////

    /**
     * @throws AmbiguousTypeException|ReflectionException
     */
    public function testHydratingArrayOfObjectsSucceeds(): void
    {
        $specificArrayKey = 36;
        $data             = [
            CarArray::regular(),
            $specificArrayKey => CarArray::regular(),
            CarArray::regular(),
        ];

        $classInfoGenerator = new ClassInfoGenerator();
        $arrayToObject      = new ArrayToObject($classInfoGenerator);

        /** @var array<int, CarComplete> $cars */
        $cars = $arrayToObject->hydrateMulti($data, CarComplete::class);

        self::assertContainsOnlyInstancesOf(CarComplete::class, $cars);
        self::assertArrayHasKey($specificArrayKey, $cars);
    }

    /**
     * @throws ReflectionException
     */
    public function testHydratingArrayOfObjectsThrowsAmbiguousTypeExceptionOnInvalidArrayOfObjectKey(): void
    {
        $data = ['test' => CarArray::regular()];

        $classInfoGenerator = new ClassInfoGenerator();
        $arrayToObject      = new ArrayToObject($classInfoGenerator);

        $this->expectException(AmbiguousTypeException::class);

        /* @phpstan-ignore-next-line */
        $arrayToObject->hydrateMulti($data, CarComplete::class);
    }

    /**
     * @throws ReflectionException
     */
    public function testHydratingArrayOfObjectsThrowsAmbiguousTypeExceptionOnInvalidArrayOfObjectArray(): void
    {
        $data = [12 => 'bert'];

        $classInfoGenerator = new ClassInfoGenerator();
        $arrayToObject      = new ArrayToObject($classInfoGenerator);

        $this->expectException(AmbiguousTypeException::class);

        /* @phpstan-ignore-next-line */
        $arrayToObject->hydrateMulti($data, CarComplete::class);
    }

    /**
     * @return array<string, array<mixed>>
     */
    public static function castToBoolSucceedsProvider(): array
    {
        return [
            'true = true'          => [true, true],
            '1 = true'             => [1, true],
            'false = false'        => [false, false],
            '0 = false'            => [0, false],
            '"0" = false'          => ['0', false],
            '"n" = false'          => ['n', false],
            '"no" = false'         => ['no', false],
            '"1" = true'           => ['1', true],
            '"y" = true'           => ['y', true],
            '"yes" = true'         => ['yes', true],
            '"randomValue" = true' => ['randomValue', true],
        ];
    }
}
