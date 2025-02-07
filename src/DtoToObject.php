<?php

declare(strict_types=1);

namespace SquidIT\Hydrator;

use SquidIT\Hydrator\Abstract\AbstractObjectToObjectHydrator;

class DtoToObject extends AbstractObjectToObjectHydrator
{
    public function hydrate(object $objectData, string $className): object
    {
        return $this->createObjectAndHydrate($objectData, $className);
    }

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
