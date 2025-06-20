<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Property;

use SquidIT\Hydrator\Exceptions\InvalidPathTrackerPositionException;

use function array_key_last;
use function count;
use function implode;
use function is_int;

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

    /**
     * @throws InvalidPathTrackerPositionException
     */
    public function setCurrentPathIteration(string $path, int|string $key): void
    {
        $this->removePath();

        $key = is_int($key) ? $key : '\'' . $key . '\'';

        $this->pathList[] = $path . '[' . $key . ']';
    }

    /**
     * @throws InvalidPathTrackerPositionException
     */
    public function removePath(): void
    {
        if (count($this->pathList) === 0) {
            throw new InvalidPathTrackerPositionException('PathTracker - Unable to remove path, path list is empty');
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
