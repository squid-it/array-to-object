<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use SquidIT\Hydrator\ArrayToObject;
use SquidIT\Hydrator\Class\ClassInfoGenerator;
use SquidIT\Hydrator\Exceptions\AmbiguousTypeException;
use SquidIT\Hydrator\Tests\Unit\ExampleArrays\CarArray;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Complete\CarComplete;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Complete\CarCompleteWithNewInConstructor;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Parts\InterCooler;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Manufacturer\Employee;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Manufacturer\Honda;

class ArrayToObjectHydratorFullTest extends TestCase
{
    /**
     * @throws ReflectionException
     * @throws AmbiguousTypeException
     */
    public function testHydratingFullObjectWithNestedElementsSucceeds(): void
    {
        $data                   = CarArray::regular();
        $classPropertyExtractor = new ClassInfoGenerator();
        $hydrator               = new ArrayToObject($classPropertyExtractor);

        /** @var CarComplete $car */
        $car = $hydrator->hydrate($data, CarComplete::class);

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

        $classPropertyExtractor = new ClassInfoGenerator();
        $hydrator               = new ArrayToObject($classPropertyExtractor);

        /** @var CarCompleteWithNewInConstructor $car */
        $car = $hydrator->hydrate($data, CarCompleteWithNewInConstructor::class);
        self::assertInstanceOf(DateTimeImmutable::class, $car->countryEntryDate);
        self::assertTrue(
            $currentTime->modify('-5 seconds') < $car->countryEntryDate
            && $currentTime->modify('+5 seconds') > $car->countryEntryDate
        );
    }
}
