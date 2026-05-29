<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Abstract;

use BackedEnum;
use DateTimeImmutable;
use JsonSerializable;
use ReflectionClass;
use ReflectionProperty;

abstract class AbstractObjectToDto implements JsonSerializable
{
    /** Override in subclasses if you need a different format. */
    protected const string DATE_TIME_FORMAT = 'Y-m-d\TH:i:s.u';

    /** @var array<class-string, list<string>> */
    private static array $propertyNameListByClassName = [];

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
        if ($value instanceof self) {
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
        if (isset(self::$propertyNameListByClassName[static::class]) === true) {
            return self::$propertyNameListByClassName[static::class];
        }

        $propertyNameList       = [];
        $reflectionClass        = new ReflectionClass(static::class);
        $reflectionPropertyList = $reflectionClass->getProperties(
            ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED
        );

        foreach ($reflectionPropertyList as $reflectionProperty) {
            if ($reflectionProperty->isStatic() === true) {
                continue;
            }

            $propertyNameList[] = $reflectionProperty->getName();
        }

        self::$propertyNameListByClassName[static::class] = $propertyNameList;

        return $propertyNameList;
    }
}
