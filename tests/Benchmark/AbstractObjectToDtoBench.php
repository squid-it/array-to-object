<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Benchmark;

use DateTimeImmutable;
use JsonException;
use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use SquidIT\Hydrator\Tests\Fixtures\Abstract\BenchmarkCarDto;
use SquidIT\Hydrator\Tests\Fixtures\Abstract\BenchmarkCarDtoFactory;
use SquidIT\Hydrator\Tests\Fixtures\Abstract\ExampleDto;
use SquidIT\Hydrator\Tests\Fixtures\Abstract\ExampleDtoState;
use Throwable;

class AbstractObjectToDtoBench
{
    private ExampleDto $flatDto;
    private BenchmarkCarDto $nestedDto;

    /**
     * @throws Throwable
     */
    public function setUp(): void
    {
        $this->flatDto = new ExampleDto(
            'example',
            new DateTimeImmutable('2026-05-18 12:34:56.123456'),
            ExampleDtoState::Ready,
            null,
            'private-token',
        );

        $benchmarkCarDtoFactory = new BenchmarkCarDtoFactory();
        $this->nestedDto        = $benchmarkCarDtoFactory->create();
    }

    #[BeforeMethods('setUp'), Revs(1000), Iterations(5), Warmup(2)]
    public function benchFlatDtoToArray(): void
    {
        $this->flatDto->toArray();
    }

    #[BeforeMethods('setUp'), Revs(1000), Iterations(5), Warmup(2)]
    public function benchFlatDtoJsonSerialize(): void
    {
        $this->flatDto->jsonSerialize();
    }

    #[BeforeMethods('setUp'), Revs(1000), Iterations(5), Warmup(2)]
    public function benchNestedDtoToArray(): void
    {
        $this->nestedDto->toArray();
    }

    #[BeforeMethods('setUp'), Revs(1000), Iterations(5), Warmup(2)]
    public function benchNestedDtoJsonSerialize(): void
    {
        $this->nestedDto->jsonSerialize();
    }

    /**
     * @throws JsonException
     */
    #[BeforeMethods('setUp'), Revs(1000), Iterations(5), Warmup(2)]
    public function benchNestedDtoJsonEncode(): void
    {
        json_encode($this->nestedDto, JSON_THROW_ON_ERROR);
    }
}
