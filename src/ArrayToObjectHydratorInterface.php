<?php

declare(strict_types=1);

namespace SquidIT\Hydrator;

use ReflectionException;
use SquidIT\Hydrator\Exceptions\AmbiguousTypeException;
use TypeError;

/**
 * @template T of object
 */
interface ArrayToObjectHydratorInterface
{
    /**
     * @param array<string, mixed> $objectData
     * @param class-string<T>      $className
     *
     * @throws AmbiguousTypeException|ReflectionException|TypeError
     *
     * @phpstan-return T
     */
    public function hydrate(array $objectData, string $className): object;

    /**
     * @param array<int, array<string, mixed>> $arrayOfObjectData
     * @param class-string<T>                  $className
     *
     * @throws AmbiguousTypeException|ReflectionException
     *
     * @return array<int, T>
     */
    public function hydrateMulti(array $arrayOfObjectData, string $className): array;
}
