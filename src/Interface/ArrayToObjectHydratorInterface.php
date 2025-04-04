<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Interface;

use ReflectionException;
use SquidIT\Hydrator\Exceptions\AmbiguousTypeException;
use SquidIT\Hydrator\Exceptions\MissingPropertyValueException;
use SquidIT\Hydrator\Exceptions\UnableToCastPropertyValueException;
use SquidIT\Hydrator\Exceptions\ValidationFailureException;

interface ArrayToObjectHydratorInterface
{
    /**
     * @template T of object
     *
     * Creates an object from array data by mapping provided array keys to corresponding typed class property names.
     *
     * The array keys must match the names of the object properties.
     *
     * @param array<string, mixed> $objectData An associative array with keys matching the class property names.
     * @param class-string<T>      $className  Classname of the object to be created
     *
     * @throws UnableToCastPropertyValueException
     * @throws ReflectionException
     * @throws AmbiguousTypeException
     * @throws MissingPropertyValueException
     * @throws ValidationFailureException
     *
     * @phpstan-return T An instance of the specified class with properties set to the corresponding array values.
     */
    public function hydrate(array $objectData, string $className): object;

    /**
     * @template T of object
     *
     * Creates an array of objects from an array of array data
     * Each array entry should contain all the array data for a single object.
     *
     * This method works the same as the hydrate() method but excepts an array of array data
     *
     * @param array<int, array<string, mixed>> $arrayOfObjectData
     * @param class-string<T>                  $className
     *
     * @throws UnableToCastPropertyValueException
     * @throws ReflectionException
     * @throws AmbiguousTypeException
     * @throws MissingPropertyValueException
     * @throws ValidationFailureException
     *
     * @return array<int, T> An array of object instances of the specified class with properties set to the corresponding array values.
     */
    public function hydrateMulti(array $arrayOfObjectData, string $className): array;
}
