<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Abstract;

use Closure;
use DateTimeImmutable;
use ReflectionClass;
use ReflectionException;
use SquidIT\Hydrator\Class\ClassInfoGenerator;
use SquidIT\Hydrator\Class\ClassProperty;
use SquidIT\Hydrator\Exceptions\AmbiguousTypeException;
use SquidIT\Hydrator\Exceptions\MissingPropertyValueException;
use SquidIT\Hydrator\Exceptions\PropertyPathBuilder;
use SquidIT\Hydrator\Exceptions\UnableToCastPropertyValueException;
use SquidIT\Hydrator\Exceptions\ValidationFailureException;
use SquidIT\Hydrator\Interface\HydratorClosureInterface;
use SquidIT\Hydrator\Interface\ObjectValidatorInterface;
use Throwable;
use TypeError;

use function array_key_last;
use function ctype_digit;
use function is_array;
use function is_bool;
use function is_int;
use function is_object;
use function is_string;
use function sprintf;

abstract class AbstractDataToObjectHydrator implements HydratorClosureInterface
{
    protected const HYDRATOR_TYPE = 'unknown';

    protected ClassInfoGenerator $classInfoGenerator;

    /** @var array<string, array<class-string, Closure>> */
    protected array $hydratorClosures = [];

    /** @var array<class-string, ReflectionClass> */
    protected array $reflectionClasses = [];

    public function __construct(ClassInfoGenerator $classInfoGenerator)
    {
        $this->classInfoGenerator = $classInfoGenerator;
    }

    /**
     * @template T of object
     *
     * @param array<int|string, mixed>|object $objectData
     * @param class-string<T>                 $className
     * @param array<string, int|null>         $objectPath
     *
     * @throws ReflectionException|TypeError
     * @throws AmbiguousTypeException
     * @throws MissingPropertyValueException|UnableToCastPropertyValueException
     * @throws ValidationFailureException
     *
     * @phpstan-return T
     */
    protected function createObjectAndHydrate(
        array|object $objectData,
        string $className,
        array $objectPath = [],
    ): object {
        if (isset($this->reflectionClasses[$className])) {
            $reflectionClass = $this->reflectionClasses[$className];
        } else {
            $reflectionClass                     = new ReflectionClass($className);
            $this->reflectionClasses[$className] = $reflectionClass;
        }

        /** @var T $object */
        $object = $reflectionClass->newInstanceWithoutConstructor();

        $classInfo      = $this->classInfoGenerator->getClassInfo($className);
        $hydrateClosure = $this->getHydratorClosure($className);
        $hydrateClosure(
            $objectData,
            $object,
            $classInfo,
            $objectPath,
        ); // start hydrating

        if ($classInfo->hasValidator) {
            /** @var ObjectValidatorInterface&T $object */
            $object->validate($objectPath);
        }

        return $object;
    }

    /**
     * @param class-string $className
     */
    protected function getHydratorClosure(string $className): Closure
    {
        if (isset($this->hydratorClosures[static::HYDRATOR_TYPE][$className])) {
            return $this->hydratorClosures[static::HYDRATOR_TYPE][$className];
        }

        $this->hydratorClosures[static::HYDRATOR_TYPE][$className] = $this->createClosure($className);

        return $this->hydratorClosures[static::HYDRATOR_TYPE][$className];
    }

    /**
     * @param array<string, int|null> $objectPath
     *
     * @throws UnableToCastPropertyValueException
     */
    public function castValue(mixed $value, ClassProperty $classProperty, array $objectPath = []): mixed
    {
        if ($value === null && $classProperty->allowsNull) {
            return null;
        }

        if (
            $classProperty->isBuildIn === false
            && $classProperty->isBackedEnum === false
            && is_object($value)
            && $value === $classProperty->defaultValue
        ) {
            return clone $value;
        }

        switch ($classProperty->type) {
            case 'int':
                if (is_int($value) === true) {
                    break;
                }

                $allDigits = ctype_digit($value);

                if ($allDigits === true) {
                    $value = (int) $value;

                    break;
                }

                if (is_string($value) === false) {
                    throw new UnableToCastPropertyValueException(sprintf(
                        'Unable to cast non int value: "%s" into %s (%s) - %s',
                        var_export($value, true),
                        PropertyPathBuilder::build($objectPath, $classProperty->name),
                        $classProperty->type,
                        'only integer values given as string can be converted to int'
                    ));
                }

                $prefix    = $value[0];
                $remainder = substr($value, 1);

                if (
                    in_array($prefix, ['-', '+'], true) === true
                    && ctype_digit($remainder) === true
                ) {
                    $value = (int) $value;
                }

                break;

            case 'bool':
                if (is_bool($value)) {
                    break;
                }

                $result = match ($value) {
                    1, 'true', '1', 'y', 'yes' => true,
                    0, 'false', '0', 'n', 'no' => false,
                    default => 'unknown',
                };

                if ($result === 'unknown') {
                    throw new UnableToCastPropertyValueException(sprintf(
                        'Unable to cast value: "%s" into %s (%s) - %s',
                        var_export($value, true),
                        PropertyPathBuilder::build($objectPath, $classProperty->name),
                        $classProperty->type,
                        'only sane boolean conversion allowed'
                    ));
                }

                $value = $result;

                break;

            case DateTimeImmutable::class:
                if (is_string($value)) {
                    try {
                        $value = new DateTimeImmutable($value);
                    } catch (Throwable $e) {
                        throw new UnableToCastPropertyValueException(sprintf(
                            'Unable to cast value: "%s" into %s (%s) - %s',
                            $value,
                            PropertyPathBuilder::build($objectPath, $classProperty->name),
                            $classProperty->type,
                            $e->getMessage()
                        ));
                    }
                }

                break;

            default:
                if ($classProperty->isBackedEnum && (is_int($value) || is_string($value))) {
                    try {
                        $enumName = $classProperty->type;
                        $value    = $enumName::from($value);
                    } catch (Throwable $e) {
                        throw new UnableToCastPropertyValueException(
                            sprintf(
                                'Unable to cast value: "%s" into %s (%s - Backed Enum) - %s',
                                $value,
                                PropertyPathBuilder::build($objectPath, $classProperty->name),
                                $classProperty->type,
                                $e->getMessage()
                            )
                        );
                    }
                }
        }

        return $value;
    }

    /**
     * Recursively Hydrate objects and array of objects
     *
     * @param array<int|string, mixed>|object $value
     * @param array<string, int|null>         $objectPath
     *
     * @throws ReflectionException|TypeError
     * @throws AmbiguousTypeException
     * @throws MissingPropertyValueException|UnableToCastPropertyValueException
     * @throws ValidationFailureException
     *
     * @phpstan-return array<int|string, mixed>|object
     */
    public function recursivelyHydrate(
        array|object $value,
        ClassProperty $classProperty,
        array $objectPath,
    ): array|object {
        $result = $value;

        if ($classProperty->isBuildIn === false) {
            // Single Object
            /** @var class-string $classString */
            $classString = $classProperty->type;
            $result      = $this->createObjectAndHydrate($value, $classString, $objectPath);
        } elseif ($classProperty->arrayOf !== null && is_array($value) === true) {
            // Array of Objects
            /** @var class-string $classString */
            $classString       = $classProperty->arrayOf;
            $arrayOfObjects    = [];
            $lastObjectPathKey = array_key_last($objectPath);

            /**
             * @var array<string, mixed> $arrayItem
             * @var int                  $key
             */
            foreach ($value as $key => $arrayItem) {
                $objectPath[$lastObjectPathKey] = $key;
                // retain the array index key
                $arrayOfObjects[$key] = $this->createObjectAndHydrate($arrayItem, $classString, $objectPath);
            }

            $result = $arrayOfObjects;
        }

        return $result;
    }
}
