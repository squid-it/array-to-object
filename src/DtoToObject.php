<?php

declare(strict_types=1);

namespace SquidIT\Hydrator;

use ReflectionException;
use SquidIT\Hydrator\Abstract\AbstractObjectToObjectHydrator;
use SquidIT\Hydrator\Exceptions\AmbiguousTypeException;
use SquidIT\Hydrator\Exceptions\MissingPropertyValueException;
use SquidIT\Hydrator\Exceptions\UnableToCastPropertyValueException;
use SquidIT\Hydrator\Exceptions\ValidationFailureException;

class DtoToObject extends AbstractObjectToObjectHydrator
{
    /**
     * @throws UnableToCastPropertyValueException
     * @throws ReflectionException
     * @throws AmbiguousTypeException
     * @throws MissingPropertyValueException
     * @throws ValidationFailureException
     */
    public function hydrate(object $objectData, string $className): object
    {
        return $this->createObjectAndHydrate($objectData, $className);
    }

    /**
     * @throws UnableToCastPropertyValueException
     * @throws ReflectionException
     * @throws AmbiguousTypeException
     * @throws MissingPropertyValueException
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
