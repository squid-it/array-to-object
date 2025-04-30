<?php

declare(strict_types=1);

namespace SquidIT\Hydrator;

use ReflectionException;
use SquidIT\Hydrator\Abstract\AbstractArrayToObjectHydrator;
use SquidIT\Hydrator\Class\ClassInfoGenerator;
use SquidIT\Hydrator\Exceptions\AmbiguousTypeException;
use SquidIT\Hydrator\Exceptions\InvalidKeyException;
use SquidIT\Hydrator\Exceptions\MissingPropertyValueException;
use SquidIT\Hydrator\Exceptions\UnableToCastPropertyValueException;
use SquidIT\Hydrator\Exceptions\ValidationFailureException;
use SquidIT\Hydrator\Property\DotNotationFormat;

class DotNotationArrayToObject extends AbstractArrayToObjectHydrator
{
    protected DotNotationFormat $dotNotationFormat;

    public function __construct(
        ClassInfoGenerator $classInfoGenerator,
        DotNotationFormat $dotNotationFormat = DotNotationFormat::JAVASCRIPT,
        bool $useEndUserSafeErrorMsg = false,
    ) {
        $this->dotNotationFormat = $dotNotationFormat;

        parent::__construct($classInfoGenerator, $useEndUserSafeErrorMsg);
    }

    /**
     * @throws AmbiguousTypeException
     * @throws MissingPropertyValueException
     * @throws UnableToCastPropertyValueException
     * @throws ValidationFailureException
     * @throws InvalidKeyException
     * @throws ReflectionException
     */
    public function hydrate(array $objectData, string $className): object
    {
        $dotNotationToMultiDimensional = new DotNotationToMultiDimensional($objectData, $this->dotNotationFormat);
        /** @var array<string, mixed> $objectData */
        $objectData = $dotNotationToMultiDimensional->convert();

        return $this->createObjectAndHydrate($objectData, $className);
    }

    /**
     * @throws AmbiguousTypeException
     * @throws InvalidKeyException
     * @throws MissingPropertyValueException
     * @throws ReflectionException
     * @throws UnableToCastPropertyValueException
     * @throws ValidationFailureException
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
