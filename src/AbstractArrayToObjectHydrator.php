<?php

declare(strict_types=1);

namespace SquidIT\Hydrator;

use BackedEnum;
use Closure;
use DateTimeImmutable;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use SquidIT\Hydrator\Class\ClassInfo;
use SquidIT\Hydrator\Class\ClassInfoGenerator;
use SquidIT\Hydrator\Class\ClassProperty;
use SquidIT\Hydrator\Exceptions\AmbiguousTypeException;
use SquidIT\Hydrator\Exceptions\MissingPropertyValueException;
use SquidIT\Hydrator\Exceptions\UnableToCastPropertyValueException;
use Throwable;
use TypeError;

use function array_key_exists;
use function array_key_first;
use function is_array;
use function is_int;
use function is_string;
use function sprintf;

abstract class AbstractArrayToObjectHydrator implements ArrayToObjectHydratorInterface
{
    protected ClassInfoGenerator $classPropertyExtractor;

    /** @var array<class-string, Closure> */
    protected array $hydratorClosures;

    /** @var array<class-string, ReflectionClass> */
    protected array $reflectionClasses = [];

    public function __construct(ClassInfoGenerator $classPropertyExtractor)
    {
        $this->classPropertyExtractor = $classPropertyExtractor;
    }

    /**
     * @param array<string, mixed> $objectData
     * @param class-string         $className
     *
     * @throws ReflectionException|TypeError
     * @throws AmbiguousTypeException
     */
    protected function createObjectAndHydrate(array $objectData, string $className): object
    {
        if (isset($this->reflectionClasses[$className])) {
            $reflectionClass = $this->reflectionClasses[$className];
        } else {
            $reflectionClass                     = new ReflectionClass($className);
            $this->reflectionClasses[$className] = $reflectionClass;
        }

        $object = $reflectionClass->newInstanceWithoutConstructor();

        $hydrateClosure = $this->getHydratorClosure($className);
        $hydrateClosure(
            $objectData,
            $object,
            $this->classPropertyExtractor->getClassInfo($className)
        ); // start hydrating

        return $object;
    }

    /**
     * @param array<int, array<string, mixed>> $multiDimensionalArray
     * @param class-string                     $className
     *
     * @throws AmbiguousTypeException
     */
    protected function checkIfMultiDimensionalArray(array $multiDimensionalArray, string $className): void
    {
        $firstArrayKey = array_key_first($multiDimensionalArray);

        if (is_int($firstArrayKey) === false || !is_array($multiDimensionalArray[$firstArrayKey])) {
            $errorMsg = sprintf(
                'Could not hydrate an Array of "%s" input array needs to be an indexed (list) of arrays',
                $className
            );

            throw new AmbiguousTypeException($errorMsg);
        }
    }

    /**
     * @param class-string $className
     */
    private function getHydratorClosure(string $className): Closure
    {
        if (isset($this->hydratorClosures[$className])) {
            return $this->hydratorClosures[$className];
        }

        $this->hydratorClosures[$className] = $this->createClosure($className);

        return $this->hydratorClosures[$className];
    }

    /**
     * @param class-string $className
     */
    private function createClosure(string $className): Closure
    {
        $hydrator = $this;
        $closure  = Closure::bind(
            static function (array $data, object $object, ClassInfo $classInfo) use ($hydrator) {
                foreach ($classInfo->classPropertyList as $propertyName => $classProperty) {
                    $value = $hydrator->getPropertyValue($data, $propertyName, $classProperty);
                    $value = $hydrator->castValue($value, $classProperty);

                    // hydrate nested objects or array of objects
                    if (is_array($value)) {
                        $value = $hydrator->recursivelyHydrate($value, $classProperty);
                    }

                    // assign value
                    $object->{$propertyName} = $value;
                }
            },
            null,
            $className
        );

        if (!($closure instanceof Closure)) {
            throw new RuntimeException('Unable to create Closure for: ' . $className);
        }

        return $closure;
    }

    /**
     * Return property value from supplied data
     * If no value exists, check if class property has got a default value and return default value
     *
     * @param array<string, mixed> $data
     *
     * @throws MissingPropertyValueException
     * @throws ReflectionException
     */
    public function getPropertyValue(array &$data, string $propertyName, ClassProperty $classProperty): mixed
    {
        $hasPropertyDataInArray = array_key_exists($propertyName, $data);

        if ($hasPropertyDataInArray === false && $classProperty->hasDefaultValue === false) {
            $msg = sprintf(
                'Could not hydrate object: "%s", no property data provided for: "%s"',
                (new ReflectionClass($classProperty->className))->getName(),
                $propertyName
            );

            throw new MissingPropertyValueException($msg);
        }

        $value = $hasPropertyDataInArray ? $data[$propertyName] : $classProperty->defaultValue;
        unset($data[$propertyName]); // speedup future array_key_exist calls

        return $value;
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
            case 'bool':
                $value = match ($value) {
                    true, 1 => true, // not merging with default arm for minor speed-up
                    false, 0, 'false', '0', 'n', 'no' => false,
                    default => true,
                };

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
                        /** @var BackedEnum $enumName */
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
     * @param array<string, mixed> $value
     *
     * @throws ReflectionException
     * @throws AmbiguousTypeException
     */
    public function recursivelyHydrate(array $value, ClassProperty $classProperty): mixed
    {
        $result = $value;

        if ($classProperty->isBuildIn === false) {
            // Single Object
            /** @var class-string $classString */
            $classString = $classProperty->type;
            $result      = $this->createObjectAndHydrate($value, $classString);
        } elseif ($classProperty->arrayOf !== null) {
            // Array of Objects
            /** @var class-string $classString */
            $classString    = $classProperty->arrayOf;
            $arrayOfObjects = [];

            /** @var array<string, mixed> $arrayItem */
            foreach ($value as $arrayItem) {
                $arrayOfObjects[] = $this->createObjectAndHydrate($arrayItem, $classString);
            }

            // set hydrated objects to our current property value
            $result = $arrayOfObjects;
        }

        return $result;
    }
}
