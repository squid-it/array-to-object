<?php

declare(strict_types=1);

namespace Property;

use PHPUnit\Framework\TestCase;
use SquidIT\Hydrator\Property\DotNotationFormat;

class DotNotationFormatTest extends TestCase
{
    public function testDotNotationFormatValuesMatchSupportedFormats(): void
    {
        self::assertSame('javascript', DotNotationFormat::JAVASCRIPT->value);
        self::assertSame('explode', DotNotationFormat::EXPLODE->value);
    }
}
