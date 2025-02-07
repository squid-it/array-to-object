<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SquidIT\Hydrator\DotNotationToMultiDimensional;
use SquidIT\Hydrator\Exceptions\InvalidKeyException;
use SquidIT\Hydrator\Property\DotNotationFormat;
use SquidIT\Hydrator\Tests\Unit\ExampleArrays\CarData;

class DotNotationToMultiDimensionalTest extends TestCase
{
    /**
     * @throws InvalidKeyException
     */
    public function testJavascriptNotationConvertSucceeds(): void
    {
        $dataExpected = CarData::regularArray();
        $dataInput    = CarData::dottedJavascript();

        $dottedToNested = new DotNotationToMultiDimensional($dataInput);
        $result         = $dottedToNested->convert();

        self::assertSame($dataExpected, $result);
    }

    /**
     * @throws InvalidKeyException
     */
    public function testExplodeNotationConvertSucceeds(): void
    {
        $dataExpected = CarData::regularArray();
        $dataInput    = CarData::dottedExplode();

        $dottedToNested = new DotNotationToMultiDimensional($dataInput, DotNotationFormat::EXPLODE);
        $result         = $dottedToNested->convert();

        self::assertSame($dataExpected, $result);
    }

    /**
     * @throws InvalidKeyException
     */
    public function testConvertingArrayOfConvertSucceeds(): void
    {
        $dataExpected = [CarData::regularArray(), CarData::regularArray()];
        $dataInput    = [];

        $loop = 0;

        while ($loop < 2) {
            foreach (CarData::dottedJavascript() as $key => $value) {
                $dataInput[sprintf('[%s].%s', $loop, $key)] = $value;
            }

            $loop++;
        }

        $dottedToNested = new DotNotationToMultiDimensional($dataInput);
        $result         = $dottedToNested->convert();

        self::assertSame($dataExpected, $result);
    }

    public function testConstructThrowsInvalidArgumentExceptionOnEmptyInputArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Dotted array can not be empty');
        new DotNotationToMultiDimensional([]);
    }
}
