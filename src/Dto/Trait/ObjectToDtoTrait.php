<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Dto\Trait;

use BackedEnum;
use DateTimeImmutable;
use ReflectionClass;
use ReflectionProperty;
use SquidIT\Hydrator\Dto\Interface\ObjectToDtoInterface;

trait ObjectToDtoTrait
{
    /** Override in composing classes if you need a different format. */
    protected const string DATE_TIME_FORMAT = 'Y-m-d\TH:i:s.u';

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $result = [];

        foreach (self::propertyNameListForClass() as $propertyName) {
            $result[$propertyName] = $this->normalizeValue($this->{$propertyName});
        }

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    private function normalizeValue(mixed $value): mixed
    {
        if ($value instanceof ObjectToDtoInterface) {
            return $value->toArray();
        }

        if ($value instanceof DateTimeImmutable) {
            return $value->format(static::DATE_TIME_FORMAT);
        }

        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        if (is_array($value) === true) {
            $normalizedValueList = [];

            foreach ($value as $key => $itemValue) {
                $normalizedValueList[$key] = $this->normalizeValue($itemValue);
            }

            return $normalizedValueList;
        }

        return $value;
    }

    /**
     * @return list<string>
     */
    private static function propertyNameListForClass(): array
    {
        /** @var array<class-string, list<string>> $cache */
        static $cache = [];

        $className = static::class;

        if (isset($cache[$className]) === true) {
            return $cache[$className];
        }

        $propertyNameList       = [];
        $reflectionClass        = new ReflectionClass($className);
        $reflectionPropertyList = $reflectionClass->getProperties(
            ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED
        );

        foreach ($reflectionPropertyList as $reflectionProperty) {
            if ($reflectionProperty->isStatic() === true) {
                continue;
            }

            $propertyNameList[] = $reflectionProperty->getName();
        }

        $cache[$className] = $propertyNameList;

        return $propertyNameList;
    }
}
