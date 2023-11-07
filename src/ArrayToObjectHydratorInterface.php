<?php

declare(strict_types=1);

namespace SquidIT\Hydrator;

use ReflectionException;
use SquidIT\Hydrator\Exceptions\AmbiguousTypeException;
use TypeError;

interface ArrayToObjectHydratorInterface
{
    /**
     * @param array<string, mixed> $objectData
     * @param class-string         $className
     *
     * @throws AmbiguousTypeException|ReflectionException|TypeError
     */
    public function hydrate(array $objectData, string $className): object;

    /**
     * @param array<int, array<string, mixed>> $arrayOfObjectData
     * @param class-string                     $className
     *
     * @throws AmbiguousTypeException|ReflectionException
     *
     * @return array<int, array<object>>
     */
    public function hydrateMulti(array $arrayOfObjectData, string $className): array;
}
