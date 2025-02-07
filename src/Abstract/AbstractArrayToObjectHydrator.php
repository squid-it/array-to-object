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
use SquidIT\Hydrator\Interface\ArrayToObjectHydratorInterface;

use function array_key_exists;
use function is_array;
use function sprintf;

abstract class AbstractArrayToObjectHydrator extends AbstractDataToObjectHydrator implements ArrayToObjectHydratorInterface
{
    protected const HYDRATOR_TYPE = 'array';

    /**
     * @param class-string $className
     */
    public function createClosure(string $className): Closure
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
     * @param array<int, array<string, mixed>|object> $multiDimensionalArray
     * @param class-string                            $className
     *
     * @throws AmbiguousTypeException
     */
    protected function checkIfMultiDimensionalArray(array $multiDimensionalArray, string $className): void
    {
        $firstArrayKey = array_key_first($multiDimensionalArray);

        if (is_int($firstArrayKey) === false || is_array($multiDimensionalArray[$firstArrayKey]) === false) {
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
}
