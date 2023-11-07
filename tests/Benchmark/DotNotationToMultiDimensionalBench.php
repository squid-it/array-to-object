<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Benchmark;

use PhpBench\Benchmark\Metadata\Annotations\Iterations;
use PhpBench\Benchmark\Metadata\Annotations\Revs;
use PhpBench\Benchmark\Metadata\Annotations\Warmup;
use SquidIT\Hydrator\DotNotationToMultiDimensional;
use SquidIT\Hydrator\Exceptions\InvalidKeyException;
use SquidIT\Hydrator\Property\DotNotationFormat;
use SquidIT\Hydrator\Tests\Unit\ExampleArrays\CarArray;

class DotNotationToMultiDimensionalBench
{
    /**
     * @Revs(1000)
     *
     * @Iterations(5)
     *
     * @Warmup(2)
     *
     * @throws InvalidKeyException
     */
    public function benchJavascriptDotNotation(): void
    {
        $dataInput = CarArray::dottedJavascript();

        $dottedToNested = new DotNotationToMultiDimensional($dataInput);
        $dottedToNested->convert();
    }

    /**
     * @Revs(3)
     *
     * @Iterations(3)
     *
     * @Warmup(1)
     *
     * @throws InvalidKeyException
     */
    public function benchJavascriptDotNotationX1000(): void
    {
        $dataInput = CarArray::dottedJavascript();

        $i = 1000;

        while ($i > 0) {
            $dottedToNested = new DotNotationToMultiDimensional($dataInput);
            $dottedToNested->convert();

            $i--;
        }
    }

    /**
     * @Revs(1000)
     *
     * @Iterations(5)
     *
     * @Warmup(2)
     *
     * @throws InvalidKeyException
     */
    public function benchExplodeDotNotation(): void
    {
        $dataInput = CarArray::dottedExplode();

        $dottedToNested = new DotNotationToMultiDimensional($dataInput, DotNotationFormat::EXPLODE);
        $dottedToNested->convert();
    }

    /**
     * @Revs(3)
     *
     * @Iterations(3)
     *
     * @Warmup(1)
     *
     * @throws InvalidKeyException
     */
    public function benchExplodeDotNotationX1000(): void
    {
        $dataInput = CarArray::dottedExplode();

        $i = 1000;

        while ($i > 0) {
            $dottedToNested = new DotNotationToMultiDimensional($dataInput, DotNotationFormat::EXPLODE);
            $dottedToNested->convert();

            $i--;
        }
    }
}
