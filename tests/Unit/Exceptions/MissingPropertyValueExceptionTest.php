<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Unit\Exceptions;

use PHPUnit\Framework\TestCase;
use SquidIT\Hydrator\ArrayToObject;
use SquidIT\Hydrator\Class\ClassInfoGenerator;
use SquidIT\Hydrator\Exceptions\MissingPropertyValueException;
use SquidIT\Hydrator\Tests\Unit\ExampleArrays\CarData;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Complete\CarComplete;
use Throwable;

class MissingPropertyValueExceptionTest extends TestCase
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
        $data = CarData::regularArray();

        /* @phpstan-ignore-next-line */
        $data['manufacturer']['employeeList'][1]['employeeNaam'] = 'Melvin';

        unset($data['manufacturer']['employeeList'][1]['employeeName']);

        $this->expectException(MissingPropertyValueException::class);
        $this->expectExceptionMessage('Could not hydrate object: "Employee", no property data provided for: "employeeName" (passengerList.manufacturer.employeeList[1].employeeName)');

        $this->arrayToObject->hydrate($data, CarComplete::class);
    }
}
