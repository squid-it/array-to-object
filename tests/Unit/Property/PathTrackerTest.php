<?php

declare(strict_types=1);

namespace Property;

use PHPUnit\Framework\TestCase;
use SquidIT\Hydrator\Exceptions\InvalidPathTrackerPositionException;
use SquidIT\Hydrator\Property\PathTracker;
use Throwable;

class PathTrackerTest extends TestCase
{
    public function testAddPathWorksAsExpected(): void
    {
        $pathList     = ['i', 'Am', 'A', 'Path'];
        $pathEnd      = 'Stop';
        $expectedPath = implode('.', $pathList) . '.' . $pathEnd;

        $pathTracker = new PathTracker();

        foreach ($pathList as $path) {
            $pathTracker->addPath($path);
        }

        self::assertEquals($expectedPath, $pathTracker->getPath($pathEnd));
    }

    public function testGetPathReturnsCurrentPropertyWhenNoPreviousPathsHaveBeenRecorded(): void
    {
        $pathEnd     = 'Stop';
        $pathTracker = new PathTracker();

        self::assertEquals($pathEnd, $pathTracker->getPath($pathEnd));
    }

    public function testRemovePathThrowsExceptionWhenNoPreviousPathsHaveBeenRecorded(): void
    {
        self::expectException(InvalidPathTrackerPositionException::class);
        self::expectExceptionMessage('PathTracker - Unable to remove path, path list is empty');

        $pathTracker = new PathTracker();
        $pathTracker->removePath();
    }

    /**
     * @throws Throwable
     */
    public function testSetCurrentPathIterationSucceedsUsingAnIndexedArrayOfObjects(): void
    {
        $indexedArray = [
            'passengerList' => [
                0 => ['username' => 'melvin'],
                1 => ['username' => 'bert'],
            ],
        ];
        $path         = array_key_first($indexedArray);
        $expectedPath = $path . '[1].username';

        $pathTracker = new PathTracker();
        $pathTracker->addPath($path);
        $pathTracker->setCurrentPathIteration($path, 0);
        $pathTracker->setCurrentPathIteration($path, 1);

        self::assertEquals($expectedPath, $pathTracker->getPath('username'));
    }

    /**
     * @throws Throwable
     */
    public function testSetCurrentPathIterationSucceedsUsingAnAssociateArrayOfObjects(): void
    {
        $indexedArray = [
            'passengerList' => [
                'melvin' => ['username' => 'melvin'],
                'bert'   => ['username' => 'bert'],
            ],
        ];
        $path         = array_key_first($indexedArray);
        $expectedPath = $path . '[\'bert\'].username';

        $pathTracker = new PathTracker();
        $pathTracker->addPath($path);
        $pathTracker->setCurrentPathIteration($path, 'melvin');
        $pathTracker->setCurrentPathIteration($path, 'bert');

        self::assertEquals($expectedPath, $pathTracker->getPath('username'));
    }
}
