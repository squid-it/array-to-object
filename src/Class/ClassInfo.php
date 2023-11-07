<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Class;

readonly class ClassInfo
{
    /**
     * @param class-string                 $className
     * @param array<string, ClassProperty> $classPropertyList
     */
    public function __construct(
        public string $className,
        public array $classPropertyList,
    ) {
    }
}
