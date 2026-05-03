<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SquidIT\Hydrator\ArrayToObject;
use SquidIT\Hydrator\Class\ClassInfoGenerator;
use SquidIT\Hydrator\Class\ClassProperty;
use SquidIT\Hydrator\DtoToObject;
use SquidIT\Hydrator\Exceptions\MissingPropertyValueException;
use SquidIT\Hydrator\Property\PathTracker;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Simple\CarWithConstructor;
use Throwable;

class HydratorPropertyValueTest extends TestCase
{
    private ArrayToObject $arrayToObject;

    private DtoToObject $dtoToObject;

    protected function setUp(): void
    {
        $classInfoGenerator  = new ClassInfoGenerator();
        $this->arrayToObject = new ArrayToObject($classInfoGenerator);
        $this->dtoToObject   = new DtoToObject($classInfoGenerator);
    }

    /**
     * @throws Throwable
     */
    public function testArrayPropertyValueReturnsExistingNullValue(): void
    {
        $classProperty = $this->nullableStringClassProperty();

        $value = $this->arrayToObject->getPropertyValue(
            ['extraInfo' => null],
            'extraInfo',
            $classProperty,
            new PathTracker(),
        );

        self::assertNull($value);
    }

    /**
     * @throws Throwable
     */
    public function testArrayPropertyValueReturnsDefaultWhenPropertyDataIsMissing(): void
    {
        $classProperty = $this->integerClassProperty(true, 3);

        $value = $this->arrayToObject->getPropertyValue(
            [],
            'nrOfDoors',
            $classProperty,
            new PathTracker(),
        );

        self::assertSame(3, $value);
    }

    /**
     * @throws Throwable
     */
    public function testArrayPropertyValueThrowsExceptionWhenRequiredPropertyDataIsMissing(): void
    {
        $classProperty = $this->integerClassProperty(false, false);

        $this->expectException(MissingPropertyValueException::class);
        $this->expectExceptionMessage(
            'Could not hydrate object: "CarWithConstructor", no property data provided for: "nrOfDoors" (nrOfDoors)'
        );

        $this->arrayToObject->getPropertyValue(
            [],
            'nrOfDoors',
            $classProperty,
            new PathTracker(),
        );
    }

    /**
     * @throws Throwable
     */
    public function testArrayPropertyValueThrowsUserFriendlyExceptionWhenRequiredPropertyDataIsMissing(): void
    {
        $classProperty = $this->integerClassProperty(false, false);
        $arrayToObject = new ArrayToObject(new ClassInfoGenerator(), true);

        $this->expectException(MissingPropertyValueException::class);
        $this->expectExceptionMessage('Path: nrOfDoors - no data supplied for required property');

        $arrayToObject->getPropertyValue(
            [],
            'nrOfDoors',
            $classProperty,
            new PathTracker(),
        );
    }

    /**
     * @throws Throwable
     */
    public function testObjectPropertyValueReturnsExistingNullValue(): void
    {
        $data            = new \stdClass();
        $data->extraInfo = null;
        $classProperty   = $this->nullableStringClassProperty();

        $value = $this->dtoToObject->getPropertyValue(
            $data,
            'extraInfo',
            $classProperty,
            new PathTracker(),
        );

        self::assertNull($value);
    }

    /**
     * @throws Throwable
     */
    public function testObjectPropertyValueReturnsDefaultWhenPropertyDataIsMissing(): void
    {
        $classProperty = $this->integerClassProperty(true, 3);

        $value = $this->dtoToObject->getPropertyValue(
            new \stdClass(),
            'nrOfDoors',
            $classProperty,
            new PathTracker(),
        );

        self::assertSame(3, $value);
    }

    /**
     * @throws Throwable
     */
    public function testObjectPropertyValueThrowsExceptionWhenRequiredPropertyDataIsMissing(): void
    {
        $classProperty = $this->integerClassProperty(false, false);

        $this->expectException(MissingPropertyValueException::class);
        $this->expectExceptionMessage(
            'Could not hydrate object: "SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Simple\CarWithConstructor", supplied object does not contain property: "nrOfDoors" (nrOfDoors)'
        );

        $this->dtoToObject->getPropertyValue(
            new \stdClass(),
            'nrOfDoors',
            $classProperty,
            new PathTracker(),
        );
    }

    /**
     * @throws Throwable
     */
    public function testObjectPropertyValueThrowsUserFriendlyExceptionWhenRequiredPropertyDataIsMissing(): void
    {
        $classProperty = $this->integerClassProperty(false, false);
        $dtoToObject   = new DtoToObject(new ClassInfoGenerator(), true);

        $this->expectException(MissingPropertyValueException::class);
        $this->expectExceptionMessage('Path: nrOfDoors - no data supplied for required property');

        $dtoToObject->getPropertyValue(
            new \stdClass(),
            'nrOfDoors',
            $classProperty,
            new PathTracker(),
        );
    }

    private function integerClassProperty(bool $hasDefaultValue, mixed $defaultValue): ClassProperty
    {
        return new ClassProperty(
            CarWithConstructor::class,
            'nrOfDoors',
            false,
            'int',
            $hasDefaultValue,
            $defaultValue,
            true,
            false,
            null,
        );
    }

    private function nullableStringClassProperty(): ClassProperty
    {
        return new ClassProperty(
            CarWithConstructor::class,
            'extraInfo',
            false,
            'string',
            false,
            false,
            true,
            true,
            null,
        );
    }
}
