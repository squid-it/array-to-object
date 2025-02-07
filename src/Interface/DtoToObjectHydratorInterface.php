<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Interface;

use ReflectionException;
use SquidIT\Hydrator\Exceptions\AmbiguousTypeException;
use TypeError;

interface DtoToObjectHydratorInterface
{
    /**
     * @template T of object
     *
     * Creates an object from provided object data by mapping provided object property names to corresponding target object property names.
     *
     * The property names of the supplied object must match the names of the target object property names.
     *
     * @param object          $objectData An object with property names matching the target object property names.
     * @param class-string<T> $className  Classname of the target object
     *
     * @throws AmbiguousTypeException|ReflectionException|TypeError
     *
     * @phpstan-return T An instance of the specified class with properties set to the corresponding array values.
     */
    public function hydrate(object $objectData, string $className): object;

    /**
     * @template T of object
     *
     * Creates an array of objects from an array of source object data
     * Each array entry should contain all the object data for a single object.
     *
     * This method works the same as the hydrate() method but excepts an array of source objects data
     *
     * @param array<int, object> $arrayOfObjectData
     * @param class-string<T>    $className
     *
     * @throws AmbiguousTypeException|ReflectionException
     *
     * @return array<int, T> An array of object instances of the specified class with properties set to the corresponding object properties.
     */
    public function hydrateMulti(array $arrayOfObjectData, string $className): array;
}
