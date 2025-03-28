<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Class;

use DateTimeInterface;
use ReflectionClass;
use ReflectionEnum;
use ReflectionException;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;
use SquidIT\Hydrator\Attributes\ArrayOf;
use SquidIT\Hydrator\Exceptions\AmbiguousTypeException;
use SquidIT\Hydrator\Interface\ObjectValidatorInterface;
use SquidIT\Hydrator\Property\PropertyDefault;
use UnitEnum;

use function class_exists;
use function implode;
use function sprintf;

class ClassInfoGenerator
{
    /** @var array<string, ClassInfo> */
    private array $classInfoList = [];

    /**
     * @param class-string $className
     *
     * @throws ReflectionException
     * @throws AmbiguousTypeException
     */
    public function getClassInfo(string $className, bool $allowNonPromotedPropertyDefaults = true): ClassInfo
    {
        if (isset($this->classInfoList[$className])) {
            return $this->classInfoList[$className];
        }

        $result          = [];
        $reflectionClass = new ReflectionClass($className);
        $properties      = $reflectionClass->getProperties();

        foreach ($properties as $property) {
            $propertyName = $property->getName();

            if ($property->hasType() === false) {
                $msg = sprintf(
                    'Could not hydrate object: "%s", ambiguous property type: "%s" all object properties need to be typed',
                    $className,
                    $propertyName
                );

                throw new AmbiguousTypeException($msg);
            }

            $reflectionType = $property->getType();

            if (
                $reflectionType instanceof ReflectionIntersectionType
                || $reflectionType instanceof ReflectionUnionType
            ) {
                $types     = $reflectionType->getTypes();
                $typesList = [];

                /** @var ReflectionNamedType $multiPartType */
                foreach ($types as $multiPartType) {
                    $typesList[] = $multiPartType->getName();
                }

                $msg = sprintf(
                    'Could not hydrate object: "%s", ambiguous property type: "%s" found for property: "%s"',
                    $className,
                    $propertyName,
                    implode('|', $typesList)
                );

                throw new AmbiguousTypeException($msg);
            }

            /** @var ReflectionNamedType $reflectionType */
            $type            = $reflectionType->getName();
            $isBuildIn       = $reflectionType->isBuiltin();
            $allowsNull      = $reflectionType->allowsNull();
            $propertyDefault = $this->retrievePropertyDefaultValue($reflectionClass, $property, $allowNonPromotedPropertyDefaults);
            $propertyArrayOf = null;
            $isBackedEnum    = false;

            if ($type === 'array') {
                $propertyArrayOf = $this->retrieveArrayOfClassName($property);
            }

            // Check if property is a "backed" enum
            if ($isBuildIn === false && class_exists($type) && (new ReflectionClass($type))->isEnum()) {
                /** @var class-string<UnitEnum> $type */
                $propertyReflectionType = new ReflectionEnum($type);
                $isBackedEnum           = ($propertyReflectionType->isEnum() && $propertyReflectionType->isBacked());
            }

            // Set buildIn property to true for datetime objects
            if ($isBuildIn === false && is_a($type, DateTimeInterface::class, true)) {
                $isBuildIn = true;
            }

            $result[$propertyName] = new ClassProperty(
                $className,
                $propertyName,
                $isBackedEnum,
                $type,
                $propertyDefault->hasDefaultValue,
                $propertyDefault->defaultValue,
                $isBuildIn,
                $allowsNull,
                $propertyArrayOf,
            );
        }

        $classInfo = new ClassInfo(
            $className,
            $result,
            is_a($className, ObjectValidatorInterface::class, true)
        );

        // store result for future requests;
        $this->classInfoList[$className] = $classInfo;

        return $classInfo;
    }

    /**
     * @param class-string|string $className
     *
     * @return $this
     */
    public function setClassInfo(string $className, ClassInfo $classInfo): self
    {
        $this->classInfoList[$className] = $classInfo;

        return $this;
    }

    /**
     * Retrieve object property default value.
     *
     * If a regular property does not have a default value, promoted property is checked for a default value
     */
    private function retrievePropertyDefaultValue(
        ReflectionClass $reflectionClass,
        ReflectionProperty $reflectionProperty,
        bool $allowNonPromotedPropertyDefaults,
    ): PropertyDefault {
        $hasDefaultValue = false;
        $defaultValue    = null;
        $propertyName    = $reflectionProperty->getName();

        if ($reflectionProperty->hasDefaultValue()) {
            $hasDefaultValue = true;
            $defaultValue    = $reflectionProperty->getDefaultValue();
        } else {
            // Check if we have got a default value present in our class constructor promoted property
            $classConstructor = $reflectionClass->getConstructor();

            if ($classConstructor !== null && $classConstructor->getNumberOfParameters() >= 1) {
                $promotedProperty      = null;
                $constructorParameters = $classConstructor->getParameters();

                foreach ($constructorParameters as $parameter) {
                    if ($propertyName === $parameter->getName()) {
                        $promotedProperty = $parameter;

                        break;
                    }
                }

                if (
                    $promotedProperty !== null
                    && ($promotedProperty->isPromoted() || $allowNonPromotedPropertyDefaults)
                    && $promotedProperty->isDefaultValueAvailable()
                ) {
                    $hasDefaultValue = true;
                    $defaultValue    = $promotedProperty->getDefaultValue();
                }
            }
        }

        return new PropertyDefault($hasDefaultValue, $defaultValue);
    }

    private function retrieveArrayOfClassName(ReflectionProperty $property): ?string
    {
        $attributes = $property->getAttributes(ArrayOf::class);

        if (empty($attributes)) {
            return null;
        }

        /** @var ArrayOf $arrayOf */
        $arrayOf = $attributes[0]->newInstance();

        return $arrayOf->className;
    }
}
