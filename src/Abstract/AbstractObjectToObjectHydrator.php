<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Abstract;

use Closure;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use SquidIT\Hydrator\Class\ClassInfo;
use SquidIT\Hydrator\Class\ClassProperty;
use SquidIT\Hydrator\Exceptions\AmbiguousTypeException;
use SquidIT\Hydrator\Exceptions\MissingPropertyValueException;
use SquidIT\Hydrator\Interface\DtoToObjectHydratorInterface;
use UnitEnum;

use function is_array;
use function sprintf;

abstract class AbstractObjectToObjectHydrator extends AbstractDataToObjectHydrator implements DtoToObjectHydratorInterface
{
    protected const HYDRATOR_TYPE = 'object';

    /**
     * @param class-string $className
     */
    public function createClosure(string $className): Closure
    {
        $hydrator = $this;
        $closure  = Closure::bind(
            static function (object $sourceData, object $object, ClassInfo $classInfo) use ($hydrator) {
                foreach ($classInfo->classPropertyList as $propertyName => $classProperty) {
                    $value = $hydrator->getPropertyValue($sourceData, $propertyName, $classProperty);
                    $value = $hydrator->castValue($value, $classProperty);

                    // hydrate nested objects or array of objects
                    if (is_array($value) || (is_object($value) && ($value instanceof UnitEnum) === false)) {
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
     * @param array<int, array<string, mixed>|object> $multiDimensionalArray
     * @param class-string                            $className
     *
     * @throws AmbiguousTypeException
     */
    protected function checkIfMultiDimensionalArray(array $multiDimensionalArray, string $className): void
    {
        $firstArrayKey = array_key_first($multiDimensionalArray);

        if (is_int($firstArrayKey) === false || is_object($multiDimensionalArray[$firstArrayKey]) === false) {
            $errorMsg = sprintf(
                'Could not hydrate an Array of "%s" input array needs to be an indexed (list) of arrays',
                $className
            );

            throw new AmbiguousTypeException($errorMsg);
        }
    }

    /**
     * Return property value from supplied data
     * If no value exists, check if class property has got a default value and return default value
     *
     * @throws MissingPropertyValueException
     * @throws ReflectionException
     */
    public function getPropertyValue(object $sourceData, string $propertyName, ClassProperty $classProperty): mixed
    {
        $objectContainsPropertyData = property_exists($sourceData, $propertyName);

        if ($objectContainsPropertyData === false && $classProperty->hasDefaultValue === false) {
            $msg = sprintf(
                'Could not hydrate object: "%s", supplied object does not contain property: "%s"',
                (new ReflectionClass($classProperty->className))->getName(),
                $propertyName
            );

            throw new MissingPropertyValueException($msg);
        }

        return $objectContainsPropertyData ? $sourceData->{$propertyName} : $classProperty->defaultValue;
    }
}
