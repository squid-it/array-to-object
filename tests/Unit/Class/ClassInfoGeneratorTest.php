<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Unit\Class;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use RuntimeException;
use SquidIT\Hydrator\Class\ClassInfo;
use SquidIT\Hydrator\Class\ClassInfoGenerator;
use SquidIT\Hydrator\Class\ClassProperty;
use SquidIT\Hydrator\Exceptions\AmbiguousTypeException;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Complete\CarComplete;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Complete\CarSmall;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Parts\InterCooler;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Simple\CarMissingPropertyType;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Simple\CarWithConstructor;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Simple\CarWithDefaultDoors;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Simple\CarWithDefaultDoorsInNonPromotedProperty;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Simple\CarWithOutConstructor;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Simple\CarWithUnionType;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Speed;
use Throwable;

class ClassInfoGeneratorTest extends TestCase
{
    /**
     * @throws Throwable
     */
    #[DataProvider('settingAndGettingClassInfoProvider')]
    public function testSettingAndGettingClassInfoReturnsCorrectClassInfo(ClassInfo $classInfo): void
    {
        $className          = $classInfo->className;
        $classInfoGenerator = new ClassInfoGenerator();
        $classInfoGenerator->setClassInfo($className, $classInfo);

        self::assertSame($classInfo, $classInfoGenerator->getClassInfo($className));
    }

    /**
     * @throws Throwable
     */
    public function testClassInfoIsCorrectlyPopulatedWithClassProperties(): void
    {
        $classInfoGenerator = new ClassInfoGenerator();
        $classInfo          = $classInfoGenerator->getClassInfo(CarComplete::class);

        $reflectionClass      = new ReflectionClass(CarComplete::class);
        $reflectionProperties = $reflectionClass->getProperties();

        self::assertSame(CarComplete::class, $classInfo->className);
        self::assertCount(count($reflectionProperties), $classInfo->classPropertyList);

        foreach ($reflectionProperties as $reflectionProperty) {
            self::assertArrayHasKey($reflectionProperty->getName(), $classInfo->classPropertyList);

            $classProperty = $classInfo->classPropertyList[$reflectionProperty->getName()];

            if (($reflectionProperty->getType() instanceof ReflectionNamedType) === false) {
                throw new RuntimeException('Detected an object without a property type');
            }

            self::assertSame($reflectionProperty->getType()->getName(), $classProperty->type);
        }
    }

    /**
     * @throws Throwable
     */
    public function testPropertyContainingArrayOfAttributeContainsCorrectArrayOfClassName(): void
    {
        $propertyName     = 'interCoolers';
        $arrayOfClassName = InterCooler::class;

        $classInfoGenerator = new ClassInfoGenerator();
        $classInfo          = $classInfoGenerator->getClassInfo(CarComplete::class);

        self::assertArrayHasKey($propertyName, $classInfo->classPropertyList);
        self::assertSame($arrayOfClassName, $classInfo->classPropertyList[$propertyName]->arrayOf);
    }

    /**
     * @throws Throwable
     */
    public function testPropertyContainingBackedEnumIsCorrectlyDetected(): void
    {
        $propertyName  = 'speedCategory';
        $backEnumClass = Speed::class;

        $classInfoGenerator = new ClassInfoGenerator();
        $classInfo          = $classInfoGenerator->getClassInfo(InterCooler::class);

        self::assertArrayHasKey($propertyName, $classInfo->classPropertyList);
        self::assertTrue($classInfo->classPropertyList[$propertyName]->isBackedEnum);
        self::assertSame($backEnumClass, $classInfo->classPropertyList[$propertyName]->type);
    }

    /**
     * @throws Throwable
     */
    public function testPropertyContainingDefaultValueIsCorrectlyDetected(): void
    {
        $propertyName = 'nrOfDoors';
        $defaultValue = 4;

        $classInfoGenerator = new ClassInfoGenerator();
        $classInfo          = $classInfoGenerator->getClassInfo(CarWithDefaultDoorsInNonPromotedProperty::class);

        self::assertArrayHasKey($propertyName, $classInfo->classPropertyList);
        self::assertTrue($classInfo->classPropertyList[$propertyName]->hasDefaultValue);
        self::assertSame($defaultValue, $classInfo->classPropertyList[$propertyName]->defaultValue);
    }

    /**
     * @throws Throwable
     */
    public function testPromotedPropertyContainingDefaultValueIsCorrectlyDetected(): void
    {
        $propertyName = 'nrOfDoors';
        $defaultValue = 3;

        $classInfoGenerator = new ClassInfoGenerator();
        $classInfo          = $classInfoGenerator->getClassInfo(CarWithDefaultDoors::class);

        self::assertArrayHasKey($propertyName, $classInfo->classPropertyList);
        self::assertTrue($classInfo->classPropertyList[$propertyName]->hasDefaultValue);
        self::assertSame($defaultValue, $classInfo->classPropertyList[$propertyName]->defaultValue);
    }

    /**
     * @throws Throwable
     */
    public function testClassWithOutTypedPropertyThrowsAmbiguousException(): void
    {
        $msg = sprintf(
            'Could not hydrate object: "%s", ambiguous property type: "%s" all object properties need to be typed',
            CarMissingPropertyType::class,
            'extraInfo'
        );

        $this->expectException(AmbiguousTypeException::class);
        $this->expectExceptionMessage($msg);

        $classInfoGenerator = new ClassInfoGenerator();
        $classInfoGenerator->getClassInfo(CarMissingPropertyType::class);
    }

    /**
     * @throws ReflectionException
     */
    public function testClassWithUnionTypedPropertyThrowsAmbiguousException(): void
    {
        $msg = sprintf(
            'Could not hydrate object: "%s", ambiguous property type: "%s" found for property: "%s"',
            CarWithUnionType::class,
            'extraInfo',
            'int|float'
        );

        $this->expectException(AmbiguousTypeException::class);
        $this->expectExceptionMessage($msg);

        $classInfoGenerator = new ClassInfoGenerator();
        $classInfoGenerator->getClassInfo(CarWithUnionType::class);
    }

    /**
     * @throws Throwable
     */
    public function testClassWithPromotedEmptyArrayHasADefaultValue(): void
    {
        $classInfoGenerator = new ClassInfoGenerator();
        $classInfo          = $classInfoGenerator->getClassInfo(CarSmall::class);

        self::assertTrue($classInfo->classPropertyList['interCoolers']->hasDefaultValue);
    }

    /**
     * @return array<string, array<ClassInfo>>
     */
    public static function settingAndGettingClassInfoProvider(): array
    {
        $classInfo1 = new ClassInfo(CarWithConstructor::class, [
            'color' => new ClassProperty(
                CarWithConstructor::class,
                'color',
                false,
                'string',
                false,
                null,
                true,
                false,
                null,
            ),
        ]);

        $classInfo2 = new ClassInfo(CarWithOutConstructor::class, [
            'color' => new ClassProperty(
                CarWithOutConstructor::class,
                'color',
                false,
                'string',
                false,
                null,
                true,
                false,
                null,
            ),
        ]);

        return [
            'classWithConstructor'    => [$classInfo1],
            'classWithOutConstructor' => [$classInfo2],
        ];
    }
}
