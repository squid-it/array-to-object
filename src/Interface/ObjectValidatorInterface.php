<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Interface;

use SquidIT\Hydrator\Exceptions\ValidationFailureException;
use SquidIT\Hydrator\Property\PathTracker;

interface ObjectValidatorInterface
{
    /**
     * @throws ValidationFailureException
     */
    public function validate(PathTracker $pathTracker): void;
}
