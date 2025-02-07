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
use SquidIT\Hydrator\Exceptions\UnableToCastPropertyValueException;
use SquidIT\Hydrator\Interface\HydratorClosureInterface;
use Throwable;
use TypeError;

use function filter_var;
use function is_array;
use function is_bool;
use function is_int;
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
     *
     * @throws ReflectionException|TypeError
     * @throws AmbiguousTypeException
     *
     * @phpstan-return T
     */
    protected function createObjectAndHydrate(array|object $objectData, string $className): object
    {
        if (isset($this->reflectionClasses[$className])) {
            $reflectionClass = $this->reflectionClasses[$className];
        } else {
            $reflectionClass                     = new ReflectionClass($className);
            $this->reflectionClasses[$className] = $reflectionClass;
        }

        /** @var T $object */
        $object = $reflectionClass->newInstanceWithoutConstructor();

        $hydrateClosure = $this->getHydratorClosure($className);
        $hydrateClosure(
            $objectData,
            $object,
            $this->classInfoGenerator->getClassInfo($className)
        ); // start hydrating

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
     * @throws UnableToCastPropertyValueException
     */
    public function castValue(mixed $value, ClassProperty $classProperty): mixed
    {
        if ($value === null && $classProperty->allowsNull) {
            return null;
        }

        switch ($classProperty->type) {
            case 'int':
                if (is_int($value) === false && filter_var($value, FILTER_VALIDATE_INT) !== false) {
                    $value = (int) $value;  /** @phpstan-ignore-line cast int string to int */
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
                        'Unable to cast value: "%s" to %s::%s (%s) - %s',
                        var_export($value, true),
                        $classProperty->className,
                        $classProperty->name,
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
                            'Unable to cast value: "%s" to %s::%s (%s) - %s',
                            $value,
                            $classProperty->className,
                            $classProperty->name,
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
                                'Unable to cast value: "%s" to %s::%s (%s - Backed Enum) - %s',
                                $value,
                                $classProperty->className,
                                $classProperty->name,
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
     *
     * @throws ReflectionException
     * @throws AmbiguousTypeException
     *
     * @phpstan-return array<int|string, mixed>|object
     */
    public function recursivelyHydrate(array|object $value, ClassProperty $classProperty): array|object
    {
        $result = $value;

        if ($classProperty->isBuildIn === false) {
            // Single Object
            /** @var class-string $classString */
            $classString = $classProperty->type;
            $result      = $this->createObjectAndHydrate($value, $classString);
        } elseif ($classProperty->arrayOf !== null && is_array($value) === true) {
            // Array of Objects
            /** @var class-string $classString */
            $classString    = $classProperty->arrayOf;
            $arrayOfObjects = [];

            /** @var array<string, mixed> $arrayItem */
            foreach ($value as $key => $arrayItem) {
                // retain the array index key
                $arrayOfObjects[$key] = $this->createObjectAndHydrate($arrayItem, $classString);
            }

            $result = $arrayOfObjects;
        }

        return $result;
    }
}
