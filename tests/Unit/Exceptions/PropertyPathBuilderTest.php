<?php

declare(strict_types=1);

namespace Exceptions;

use PHPUnit\Framework\TestCase;
use SquidIT\Hydrator\Exceptions\PropertyPathBuilder;

class PropertyPathBuilderTest extends TestCase
{
    public function testBuildGeneratesProperPathWhenPathIsEmpty(): void
    {
        $propertyName   = 'employeeName';
        $expectedResult = $propertyName;
        $pathData       = [];

        self::assertSame($expectedResult, PropertyPathBuilder::build($pathData, $propertyName));
    }

    public function testBuildGeneratesProperIndexedPath(): void
    {
        $index          = 2;
        $propertyName   = 'employeeName';
        $expectedResult = 'passengerList.manufacturer.employeeList[' . $index . '].' . $propertyName;
        $pathData       = [
            'passengerList' => null,
            'manufacturer'  => null,
            'employeeList'  => $index,
        ];

        self::assertSame($expectedResult, PropertyPathBuilder::build($pathData, $propertyName));
    }
}
