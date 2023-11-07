<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Unit;

use PHPUnit\Framework\TestCase;
use ReflectionException;
use SquidIT\Hydrator\Class\ClassInfoGenerator;
use SquidIT\Hydrator\DotNotationArrayToObject;
use SquidIT\Hydrator\Exceptions\AmbiguousTypeException;
use SquidIT\Hydrator\Exceptions\InvalidKeyException;
use SquidIT\Hydrator\Property\DotNotationFormat;
use SquidIT\Hydrator\Tests\Unit\ExampleArrays\CarArray;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Complete\CarComplete;

class DotNotationArrayToObjectTest extends TestCase
{
    /**
     * @throws AmbiguousTypeException|ReflectionException
     * @throws InvalidKeyException
     */
    public function testHydratingDotArrayOfObjectsSucceeds(): void
    {
        $specificArrayKey = 36;
        $data             = [
            CarArray::dottedJavascript(),
            $specificArrayKey => CarArray::dottedJavascript(),
            CarArray::dottedJavascript(),
        ];

        $classInfoGenerator = new ClassInfoGenerator();
        $arrayToObject      = new DotNotationArrayToObject($classInfoGenerator, DotNotationFormat::JAVASCRIPT);

        /** @var array<int, CarComplete> $cars */
        $cars = $arrayToObject->hydrateMulti($data, CarComplete::class);

        self::assertContainsOnlyInstancesOf(CarComplete::class, $cars);
        self::assertArrayHasKey($specificArrayKey, $cars);
    }
}
