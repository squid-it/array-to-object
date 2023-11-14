<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Class;

readonly class ClassProperty
{
    /**
     * @param class-string             $className
     * @param class-string|string      $type
     * @param class-string|string|null $arrayOf
     */
    public function __construct(
        public string $className,
        public string $name,
        public bool $isBackedEnum,
        public string $type,
        public bool $hasDefaultValue,
        public mixed $defaultValue,
        public bool $isBuildIn,
        public bool $allowsNull,
        public ?string $arrayOf
    ) {
    }
}
