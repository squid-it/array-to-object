<?php

declare(strict_types=1);

namespace SquidIT\Hydrator;

use ReflectionException;
use SquidIT\Hydrator\Exceptions\AmbiguousTypeException;
use TypeError;

class ArrayToObject extends AbstractArrayToObjectHydrator
{
    /**
     * @param array<string, mixed> $objectData
     * @param class-string         $className
     *
     * @throws ReflectionException|TypeError
     * @throws AmbiguousTypeException
     */
    public function hydrate(array $objectData, string $className): object
    {
        return $this->createObjectAndHydrate($objectData, $className);
    }

    /**
     * @param array<int, array<string, mixed>> $arrayOfObjectData
     * @param class-string                     $className
     *
     * @throws AmbiguousTypeException
     * @throws ReflectionException
     *
     * @return array<int, array<object>>
     */
    public function hydrateMulti(array $arrayOfObjectData, string $className): array
    {
        $this->checkIfMultiDimensionalArray($arrayOfObjectData, $className);

        foreach ($arrayOfObjectData as $key => $objectData) {
            $arrayOfObjectData[$key] = $this->hydrate($objectData, $className);
        }

        /* @phpstan-ignore-next-line - because of Closure usage unable to detect proper return value */
        return $arrayOfObjectData;
    }
}
