<?php

declare(strict_types=1);

namespace SquidIT\Hydrator;

use ReflectionException;
use SquidIT\Hydrator\Class\ClassInfoGenerator;
use SquidIT\Hydrator\Exceptions\AmbiguousTypeException;
use SquidIT\Hydrator\Exceptions\InvalidKeyException;
use SquidIT\Hydrator\Property\DotNotationFormat;

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
     * @param class-string         $className
     *
     * @throws AmbiguousTypeException|InvalidKeyException|ReflectionException
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
     * @param class-string                     $className
     *
     * @throws AmbiguousTypeException
     * @throws ReflectionException
     * @throws InvalidKeyException
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
