<?php

declare(strict_types=1);

namespace SquidIT\Hydrator;

use ReflectionException;
use SquidIT\Hydrator\Exceptions\AmbiguousTypeException;
use TypeError;

/**
 * @template T of object
 */
class ArrayToObject extends AbstractArrayToObjectHydrator
{
    /**
     * @param array<string, mixed> $objectData
     * @param class-string<T>      $className
     *
     * @throws AmbiguousTypeException|ReflectionException|TypeError
     *
     * @phpstan-return T
     */
    public function hydrate(array $objectData, string $className): object
    {
        return $this->createObjectAndHydrate($objectData, $className);
    }

    /**
     * @param array<int, array<string, mixed>> $arrayOfObjectData
     * @param class-string<T>                  $className
     *
     * @throws AmbiguousTypeException|ReflectionException|TypeError
     *
     * @return array<int, T>
     */
    public function hydrateMulti(array $arrayOfObjectData, string $className): array
    {
        $this->checkIfMultiDimensionalArray($arrayOfObjectData, $className);

        $result = [];

        foreach ($arrayOfObjectData as $key => $objectData) {
            $result[$key] = $this->hydrate($objectData, $className);
        }

        return $result;
    }
}
