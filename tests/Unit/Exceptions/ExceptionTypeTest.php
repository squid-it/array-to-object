<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Unit\Exceptions;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SquidIT\Hydrator\Exceptions\AmbiguousTypeException;
use SquidIT\Hydrator\Exceptions\InvalidKeyException;
use SquidIT\Hydrator\Exceptions\InvalidPathTrackerPositionException;
use SquidIT\Hydrator\Exceptions\MissingPropertyValueException;
use SquidIT\Hydrator\Exceptions\ObjectHydratorException;
use SquidIT\Hydrator\Exceptions\UnableToCastPropertyValueException;
use SquidIT\Hydrator\Exceptions\ValidationFailureException;

class ExceptionTypeTest extends TestCase
{
    /**
     * @param class-string<ObjectHydratorException> $exceptionClass
     */
    #[DataProvider('objectHydratorExceptionProvider')]
    public function testExceptionExtendsObjectHydratorException(string $exceptionClass): void
    {
        self::assertInstanceOf(ObjectHydratorException::class, new $exceptionClass('test'));
    }

    /**
     * @return array<string, array{class-string<ObjectHydratorException>}>
     */
    public static function objectHydratorExceptionProvider(): array
    {
        return [
            'ambiguous type'       => [AmbiguousTypeException::class],
            'invalid key'          => [InvalidKeyException::class],
            'invalid path tracker' => [InvalidPathTrackerPositionException::class],
            'missing property'     => [MissingPropertyValueException::class],
            'unable to cast value' => [UnableToCastPropertyValueException::class],
            'validation failure'   => [ValidationFailureException::class],
        ];
    }
}
