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

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $result = [];

        // Get all class public and protected properties (private properties are excluded)
        $reflectionClass = new ReflectionClass($this);
        $properties      = $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED);

        /**
         * For each object property, create a key value pair in the final array
         */
        foreach ($properties as $property) {
            $value         = null;
            $originalValue = $property->getValue($this);

            if ($originalValue instanceof DateTimeImmutable) {
                $value = $originalValue->format(static::DATE_TIME_FORMAT);
            }

            if ($value === null && $originalValue instanceof BackedEnum) {
                $value = $originalValue->value;
            }

            if ($value === null) {
                $value = $originalValue;
            }

            $result[$property->getName()] = $value;
        }

        return $result;
    }
}
