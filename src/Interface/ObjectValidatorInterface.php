<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Interface;

use SquidIT\Hydrator\Exceptions\ValidationFailureException;

interface ObjectValidatorInterface
{
    /**
     * @param array<string, int|null> $objectPath
     *
     * @throws ValidationFailureException
     */
    public function validate(array $objectPath = []): void;
}
