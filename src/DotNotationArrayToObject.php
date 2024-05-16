<?php

declare(strict_types=1);

namespace SquidIT\Hydrator;

use ReflectionException;
use SquidIT\Hydrator\Class\ClassInfoGenerator;
use SquidIT\Hydrator\Exceptions\AmbiguousTypeException;
use SquidIT\Hydrator\Exceptions\InvalidKeyException;
use SquidIT\Hydrator\Property\DotNotationFormat;
use TypeError;

/**
 * @template T of object
 */
class DotNotationArrayToObject extends AbstractArrayToObjectHydrator
{
    protected DotNotationFormat $dotNotationFormat;

    public function __construct(
        ClassInfoGenerator $classInfoGenerator,
        DotNotationFormat $dotNotationFormat = DotNotationFormat::JAVASCRIPT
    ) {
        $this->dotNotationFormat = $dotNotationFormat;

        parent::__construct($classInfoGenerator);
    }

    /**
     * @param array<string, mixed> $objectData
     * @param class-string<T>      $className
     *
     * @throws AmbiguousTypeException|InvalidKeyException|ReflectionException|TypeError
     *
     * @phpstan-return T
     */
    public function hydrate(array $objectData, string $className): object
    {
        $dotNotationToMultiDimensional = new DotNotationToMultiDimensional($objectData, $this->dotNotationFormat);
        /** @var array<string, mixed> $objectData */
        $objectData = $dotNotationToMultiDimensional->convert();

        return $this->createObjectAndHydrate($objectData, $className);
    }

    /**
     * @param array<int, array<string, mixed>> $arrayOfObjectData
     * @param class-string<T>                  $className
     *
     * @throws AmbiguousTypeException|InvalidKeyException|ReflectionException|TypeError
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
