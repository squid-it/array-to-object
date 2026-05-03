<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Class;

use Closure;
use ReflectionClass;

readonly class HydrationPlan
{
    /**
     * @param ReflectionClass<object> $reflectionClass
     */
    public function __construct(
        public ClassInfo $classInfo,
        public Closure $hydrateClosure,
        public ReflectionClass $reflectionClass,
    ) {}
}
