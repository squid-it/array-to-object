<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Fixtures;

use SquidIT\Hydrator\DtoToObject;
use SquidIT\Hydrator\Exceptions\AmbiguousTypeException;

class DtoToObjectInputChecker extends DtoToObject
{
    /**
     * @param array<mixed> $data
     * @param class-string $className
     *
     * @throws AmbiguousTypeException
     */
    public function checkArrayOfObjectsInput(array $data, string $className): void
    {
        $this->checkIfMultiDimensionalArray($data, $className);
    }
}
