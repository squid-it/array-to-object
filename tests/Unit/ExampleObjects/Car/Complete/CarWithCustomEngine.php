<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Complete;

use SquidIT\Hydrator\Exceptions\PropertyPathBuilder;
use SquidIT\Hydrator\Exceptions\ValidationFailureException;
use SquidIT\Hydrator\Interface\ObjectValidatorInterface;

class CarWithCustomEngine implements ObjectValidatorInterface
{
    public function __construct(
        public int $engineDisplacementInCc,
    ) {}

    public function validate(array $objectPath = []): void
    {
        if ($this->engineDisplacementInCc > 8000 || $this->engineDisplacementInCc < 600) {
            $propertyPath = PropertyPathBuilder::build($objectPath, 'engineDisplacementInCc');

            throw new ValidationFailureException(
                sprintf('Invalid value received for property: %s, value needs to be between 600 and 8000', $propertyPath)
            );
        }
    }
}
