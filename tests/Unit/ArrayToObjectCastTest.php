<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SquidIT\Hydrator\ArrayToObject;
use SquidIT\Hydrator\Class\ClassInfoGenerator;
use SquidIT\Hydrator\Class\ClassProperty;
use SquidIT\Hydrator\Exceptions\UnableToCastPropertyValueException;
use SquidIT\Hydrator\Property\PathTracker;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Simple\CarWithCreatedDate;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Simple\CarWithDefaultDoors;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Simple\CarWithEnumProperty;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Speed;

class ArrayToObjectCastTest extends TestCase
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
    #[DataProvider('castToIntSucceedsProvider')]
    public function testCastToIntSucceeds(int|string $inputValue, int $expected): void
    {
        $reflectionClass    = new ReflectionClass(CarWithDefaultDoors::class);
        $reflectionProperty = $reflectionClass->getProperty('nrOfDoors');

        /** @var ReflectionNamedType $reflectionPropertyType */
        $reflectionPropertyType = $reflectionProperty->getType();

        $classProperty = new ClassProperty(
            $reflectionClass->name,
            $reflectionProperty->getName(),
            false,
            $reflectionPropertyType->getName(),
            true,
            3,
            $reflectionPropertyType->isBuiltin(),
            $reflectionPropertyType->allowsNull(),
            null
        );

        $value = $this->arrayToObject->castValue($inputValue, $classProperty);

        self::assertSame($expected, $value);
    }

    /**
     * @throws Throwable
     */
    public function testCastToIntThrowsThrowsUnableToCastPropertyValueException(): void
    {
        $inputValue         = true;
        $reflectionClass    = new ReflectionClass(CarWithDefaultDoors::class);
        $reflectionProperty = $reflectionClass->getProperty('nrOfDoors');

        /** @var ReflectionNamedType $reflectionPropertyType */
        $reflectionPropertyType = $reflectionProperty->getType();

        $classProperty = new ClassProperty(
            $reflectionClass->name,
            $reflectionProperty->getName(),
            false,
            $reflectionPropertyType->getName(),
            true,
            3,
            $reflectionPropertyType->isBuiltin(),
            $reflectionPropertyType->allowsNull(),
            null
        );

        $this->expectException(UnableToCastPropertyValueException::class);
        $this->expectExceptionMessage(
            'Unable to cast non int value: "true" into nrOfDoors (int) - only integer values given as string can be converted to int'
        );

        $this->arrayToObject->castValue($inputValue, $classProperty);
    }

    /**
     * @throws Throwable
     */
    public function testCastToIntThrowsThrowsUnableToCastPropertyValueExceptionWithUserFriendlyMessage(): void
    {
        $inputValue         = true;
        $reflectionClass    = new ReflectionClass(CarWithDefaultDoors::class);
        $reflectionProperty = $reflectionClass->getProperty('nrOfDoors');

        /** @var ReflectionNamedType $reflectionPropertyType */
        $reflectionPropertyType = $reflectionProperty->getType();

        $classProperty = new ClassProperty(
            $reflectionClass->name,
            $reflectionProperty->getName(),
            false,
            $reflectionPropertyType->getName(),
            true,
            3,
            $reflectionPropertyType->isBuiltin(),
            $reflectionPropertyType->allowsNull(),
            null
        );

        $this->expectException(UnableToCastPropertyValueException::class);
        $this->expectExceptionMessage(
            'Path: nrOfDoors - Invalid value detected. Expected integer, received: "true"'
        );

        $arrayToObject = new ArrayToObject(new ClassInfoGenerator(), true);
        $arrayToObject->castValue($inputValue, $classProperty);
    }

    /**
     * @throws Throwable
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

        $value = $this->arrayToObject->castValue($inputValue, $classProperty);

        self::assertSame($expected, $value);
    }

    public function testCastToBoolThrowsUnableToCastPropertyValueExceptionOnUnknownCastInput(): void
    {
        $value              = 'CastThis';
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

        $pathTracker  = new PathTracker(['test']);
        $exceptionMsg = sprintf(
            'Unable to cast value: "%s" into %s (%s) - %s',
            var_export($value, true),
            $pathTracker->getPath($classProperty->name),
            $classProperty->type,
            'only sane boolean conversion allowed'
        );

        $this->expectException(UnableToCastPropertyValueException::class);
        $this->expectExceptionMessage($exceptionMsg);

        $this->arrayToObject->castValue($value, $classProperty, $pathTracker);
    }

    public function testCastToBoolThrowsUnableToCastPropertyValueExceptionOnUnknownCastInputWithUserFriendlyMessage(): void
    {
        $value              = 'CastThis';
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

        $pathTracker  = new PathTracker(['test']);
        $exceptionMsg = sprintf(
            'Path: %s - Invalid value detected. Expected boolean compatible value, received: "%s"',
            $pathTracker->getPath($classProperty->name),
            var_export($value, true),
        );

        $this->expectException(UnableToCastPropertyValueException::class);
        $this->expectExceptionMessage($exceptionMsg);

        $arrayToObject = new ArrayToObject(new ClassInfoGenerator(), true);
        $arrayToObject->castValue($value, $classProperty, $pathTracker);
    }

    /**
     * @throws Throwable
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

        $value = $this->arrayToObject->castValue($dateTimeString, $classProperty);

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

        $pathTracker  = new PathTracker();
        $exceptionMsg = sprintf(
            'Unable to cast value: "%s" into %s (%s) - %s',
            $dateTimeString,
            $pathTracker->getPath($reflectionProperty->getName()),
            $reflectionPropertyType->getName(),
            'Failed to parse time string (20-error-23-01-01 12:00:00.48596) at position 0 (2): Unexpected character'
        );

        $this->expectException(UnableToCastPropertyValueException::class);
        $this->expectExceptionMessage($exceptionMsg);

        $this->arrayToObject->castValue($dateTimeString, $classProperty);
    }

    public function testCastToDateTimeImmutableThrowsUnableToCastPropertyValueExceptionOnInvalidDateTimeWithUserFriendlyMessage(): void
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

        $pathTracker  = new PathTracker();
        $exceptionMsg = sprintf(
            'Path: %s - Invalid value detected. Expected datetime compatible value, received: "%s"',
            $pathTracker->getPath($classProperty->name),
            var_export($dateTimeString, true),
        );

        $this->expectException(UnableToCastPropertyValueException::class);
        $this->expectExceptionMessage($exceptionMsg);

        $arrayToObject = new ArrayToObject(new ClassInfoGenerator(), true);
        $arrayToObject->castValue($dateTimeString, $classProperty);
    }

    /**
     * @throws Throwable
     */
    public function testCastToEnumSucceeds(): void
    {
        $enumBackedValue    = 'fast';
        $enumExpected       = Speed::FAST;
        $reflectionClass    = new ReflectionClass(CarWithEnumProperty::class);
        $reflectionProperty = $reflectionClass->getProperty('speed');

        /** @var ReflectionNamedType $reflectionPropertyType */
        $reflectionPropertyType = $reflectionProperty->getType();

        /** @var class-string<UnitEnum> $enumTypeName */
        $enumTypeName = $reflectionPropertyType->getName();

        $reflectionEnum = new ReflectionEnum($enumTypeName);

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

        $value = $this->arrayToObject->castValue($enumBackedValue, $classProperty);

        self::assertSame($enumExpected, $value);
    }

    /**
     * @throws Throwable
     */
    public function testCastToEnumThrowsUnableToCastPropertyValueExceptionOnInvalidBackedEnumValue(): void
    {
        $enumBackedValue    = 'normal';
        $reflectionClass    = new ReflectionClass(CarWithEnumProperty::class);
        $reflectionProperty = $reflectionClass->getProperty('speed');

        /** @var ReflectionNamedType $reflectionPropertyType */
        $reflectionPropertyType = $reflectionProperty->getType();

        /** @var class-string<UnitEnum> $enumTypeName */
        $enumTypeName = $reflectionPropertyType->getName();

        $reflectionEnum = new ReflectionEnum($enumTypeName);

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

        $pathTracker  = new PathTracker();
        $exceptionMsg = sprintf(
            'Unable to cast value: "%s" into %s (%s - Backed Enum) - %s',
            $enumBackedValue,
            $pathTracker->getPath($reflectionProperty->getName()),
            $reflectionPropertyType->getName(),
            '"normal" is not a valid backing value for enum SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Speed'
        );

        $this->expectException(UnableToCastPropertyValueException::class);
        $this->expectExceptionMessage($exceptionMsg);

        $this->arrayToObject->castValue($enumBackedValue, $classProperty);
    }

    /**
     * @throws Throwable
     */
    public function testCastToEnumThrowsUnableToCastPropertyValueExceptionOnInvalidBackedEnumValueWithUserFriendlyMessage(): void
    {
        $enumBackedValue    = 'normal';
        $reflectionClass    = new ReflectionClass(CarWithEnumProperty::class);
        $reflectionProperty = $reflectionClass->getProperty('speed');

        /** @var ReflectionNamedType $reflectionPropertyType */
        $reflectionPropertyType = $reflectionProperty->getType();

        /** @var class-string<UnitEnum> $enumTypeName */
        $enumTypeName = $reflectionPropertyType->getName();

        $reflectionEnum = new ReflectionEnum($enumTypeName);

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

        $pathTracker = new PathTracker();
        $enumName    = $classProperty->type;

        $exceptionMsg = sprintf(
            'Path: %s - Invalid value detected. Allowed values "%s", received: "%s"',
            $pathTracker->getPath($classProperty->name),
            implode('/', array_column($enumName::cases(), 'value')),
            var_export($enumBackedValue, true),
        );

        $this->expectException(UnableToCastPropertyValueException::class);
        $this->expectExceptionMessage($exceptionMsg);

        $arrayToObject = new ArrayToObject(new ClassInfoGenerator(), true);
        $arrayToObject->castValue($enumBackedValue, $classProperty);
    }

    /**
     * @return array<string, array<mixed>>
     */
    public static function castToBoolSucceedsProvider(): array
    {
        return [
            'true = true'   => [true, true],
            '1 = true'      => [1, true],
            '"1" = true'    => ['1', true],
            '"y" = true'    => ['y', true],
            '"yes" = true'  => ['yes', true],
            'false = false' => [false, false],
            '0 = false'     => [0, false],
            '"0" = false'   => ['0', false],
            '"n" = false'   => ['n', false],
            '"no" = false'  => ['no', false],
        ];
    }

    /**
     * @return array<string, array<int|string>>
     */
    public static function castToIntSucceedsProvider(): array
    {
        return [
            '"33" = 33'  => ['33', 33],
            '1 = 1'      => [1, 1],
            '"-2" = -2'  => ['-2', -2],
            '"0" = 0'    => ['0', 0],
            '"-0" = 0'   => ['-0', 0],
            '0 = 0'      => [0, 0],
            '"+12" = 12' => ['+12', 12],
        ];
    }
}
