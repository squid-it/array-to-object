<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Interface;

use Closure;

interface HydratorClosureInterface
{
    /**
     * @param class-string $className
     */
    public function createClosure(string $className): Closure;
}
