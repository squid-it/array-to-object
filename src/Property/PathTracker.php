<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Property;

use RuntimeException;

use function array_key_last;
use function count;
use function implode;

class PathTracker
{
    /**
     * @param array<int, string> $pathList
     */
    public function __construct(
        private array $pathList = [],
    ) {}

    public function addPath(string $path): void
    {
        $this->pathList[] = $path;
    }

    public function setCurrentPathIteration(string $path, int $key): void
    {
        $this->removePath();

        $this->pathList[] = $path . '[' . $key . ']';
    }

    public function removePath(): void
    {
        if (count($this->pathList) === 0) {
            throw new RuntimeException('PathTracker - Unable to remove path, path list is empty');
        }

        unset($this->pathList[array_key_last($this->pathList)]);
    }

    public function getPath(string $currentProperty): string
    {
        if (empty($this->pathList) === true) {
            return $currentProperty;
        }

        return implode('.', $this->pathList) . '.' . $currentProperty;
    }
}
