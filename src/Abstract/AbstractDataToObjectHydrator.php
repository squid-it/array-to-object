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
use SquidIT\Hydrator\Exceptions\UnableToCastPropertyValueException;
use SquidIT\Hydrator\Exceptions\ValidationFailureException;
use SquidIT\Hydrator\Interface\HydratorClosureInterface;
use SquidIT\Hydrator\Interface\ObjectValidatorInterface;
use SquidIT\Hydrator\Property\PathTracker;
use Throwable;
use TypeError;

use function ctype_digit;
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

    /** @var bool when true, it this library will return end-user safe error messages */
    protected bool $useEndUserSafeErrorMsg;

    public function __construct(ClassInfoGenerator $classInfoGenerator, bool $useEndUserSafeErrorMsg = false)
    {
        $this->classInfoGenerator     = $classInfoGenerator;
        $this->useEndUserSafeErrorMsg = $useEndUserSafeErrorMsg;
    }

    /**
     * @template T of object
     *
     * @param array<int|string, mixed>|object $objectData
     * @param class-string<T>                 $className
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
        PathTracker $pathTracker = new PathTracker(),
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
            $pathTracker,
        ); // start hydrating

        if ($classInfo->hasValidator) {
            /** @var ObjectValidatorInterface&T $object */
            $object->validate($pathTracker);
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
     * @throws UnableToCastPropertyValueException
     */
    public function castValue(
        mixed $value,
        ClassProperty $classProperty,
        PathTracker $pathTracker = new PathTracker(),
    ): mixed {
        if ($value === null && $classProperty->allowsNull) {
            return null;
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
                    if ($this->useEndUserSafeErrorMsg) {
                        $errorMsg = sprintf(
                            'Path: %s - Invalid value detected. Expected integer, received: "%s"',
                            $pathTracker->getPath($classProperty->name),
                            var_export($value, true),
                        );
                    } else {
                        $errorMsg = sprintf(
                            'Unable to cast non int value: "%s" into %s (%s) - %s',
                            var_export($value, true),
                            $pathTracker->getPath($classProperty->name),
                            $classProperty->type,
                            'only integer values given as string can be converted to int'
                        );
                    }

                    throw new UnableToCastPropertyValueException($errorMsg);
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
                    if ($this->useEndUserSafeErrorMsg) {
                        $errorMsg = sprintf(
                            'Path: %s - Invalid value detected. Expected boolean compatible value, received: "%s"',
                            $pathTracker->getPath($classProperty->name),
                            var_export($value, true),
                        );
                    } else {
                        $errorMsg = sprintf(
                            'Unable to cast value: "%s" into %s (%s) - %s',
                            var_export($value, true),
                            $pathTracker->getPath($classProperty->name),
                            $classProperty->type,
                            'only sane boolean conversion allowed'
                        );
                    }

                    throw new UnableToCastPropertyValueException($errorMsg);
                }

                $value = $result;

                break;

            case DateTimeImmutable::class:
                if (is_string($value)) {
                    try {
                        $value = new DateTimeImmutable($value);
                    } catch (Throwable $e) {
                        if ($this->useEndUserSafeErrorMsg) {
                            $errorMsg = sprintf(
                                'Path: %s - Invalid value detected. Expected datetime compatible value, received: "%s"',
                                $pathTracker->getPath($classProperty->name),
                                var_export($value, true),
                            );
                        } else {
                            $errorMsg = sprintf(
                                'Unable to cast value: "%s" into %s (%s) - %s',
                                $value,
                                $pathTracker->getPath($classProperty->name),
                                $classProperty->type,
                                $e->getMessage()
                            );
                        }

                        throw new UnableToCastPropertyValueException($errorMsg);
                    }
                }

                break;

            default:
                if ($classProperty->isBackedEnum && (is_int($value) || is_string($value))) {
                    $enumName = $classProperty->type;

                    try {
                        $value = $enumName::from($value);
                    } catch (Throwable $e) {
                        if ($this->useEndUserSafeErrorMsg) {
                            $errorMsg = sprintf(
                                'Path: %s - Invalid value detected. Allowed values "%s", received: "%s"',
                                $pathTracker->getPath($classProperty->name),
                                implode('/', array_column($enumName::cases(), 'value')),
                                var_export($value, true),
                            );
                        } else {
                            $errorMsg = sprintf(
                                'Unable to cast value: "%s" into %s (%s - Backed Enum) - %s',
                                $value,
                                $pathTracker->getPath($classProperty->name),
                                $classProperty->type,
                                $e->getMessage()
                            );
                        }

                        throw new UnableToCastPropertyValueException($errorMsg);
                    }
                } elseif (
                    $classProperty->isBuildIn === false
                    && $classProperty->hasDefaultValue === true
                    && $value === $classProperty->defaultValue
                ) {
                    /** @var object $value */
                    return clone $value;
                }
        }

        return $value;
    }

    /**
     * Recursively Hydrate objects and array of objects
     *
     * @param array<int|string, mixed>|object $value
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
        PathTracker $pathTracker,
    ): array|object {
        $result = $value;
        $path   = $classProperty->name;

        if ($classProperty->isBuildIn === false) {
            // Single Object
            $pathTracker->addPath($path);
            /** @var class-string $classString */
            $classString = $classProperty->type;
            $result      = $this->createObjectAndHydrate($value, $classString, $pathTracker);

            $pathTracker->removePath();
        } elseif ($classProperty->arrayOf !== null && is_array($value) === true) {
            // Array of Objects
            $pathTracker->addPath($path);
            /** @var class-string $classString */
            $classString    = $classProperty->arrayOf;
            $arrayOfObjects = [];

            /**
             * @var array<string, mixed> $arrayItem
             * @var int                  $key
             */
            foreach ($value as $key => $arrayItem) {
                $pathTracker->setCurrentPathIteration($path, $key);
                // retain the array index key
                $arrayOfObjects[$key] = $this->createObjectAndHydrate($arrayItem, $classString, $pathTracker);
            }

            $result = $arrayOfObjects;

            $pathTracker->removePath();
        }

        return $result;
    }
}
