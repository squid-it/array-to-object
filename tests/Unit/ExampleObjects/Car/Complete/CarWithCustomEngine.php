<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Complete;

use SquidIT\Hydrator\Exceptions\ValidationFailureException;
use SquidIT\Hydrator\Interface\ObjectValidatorInterface;
use SquidIT\Hydrator\Property\PathTracker;

class CarWithCustomEngine implements ObjectValidatorInterface
{
    public function __construct(
        public int $engineDisplacementInCc,
    ) {}

    public function validate(PathTracker $pathTracker): void
    {
        if ($this->engineDisplacementInCc > 8000 || $this->engineDisplacementInCc < 600) {
            $propertyPath = $pathTracker->getPath('engineDisplacementInCc');

            throw new ValidationFailureException(
                sprintf('Invalid value received for property: %s, value needs to be between 600 and 8000', $propertyPath)
            );
        }
    }
}
