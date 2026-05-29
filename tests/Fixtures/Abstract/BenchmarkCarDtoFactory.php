<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Fixtures\Abstract;

use DateTimeImmutable;

final class BenchmarkCarDtoFactory
{
    public function create(): BenchmarkCarDto
    {
        return new BenchmarkCarDto(
            'black',
            4,
            16.3,
            ['melvin', 'bert', 'cecil', 'sara'],
            new BenchmarkManufacturerDto(
                'Beautiful Street 123',
                'Apartment 1234',
                'Rotterdam',
                [
                    new BenchmarkEmployeeDto('cecil', BenchmarkDtoState::Ready),
                    new BenchmarkEmployeeDto('melvin', BenchmarkDtoState::Ready),
                    new BenchmarkEmployeeDto('sara', BenchmarkDtoState::Archived),
                ],
                new DateTimeImmutable('2012-04-03 12:13:14.123456'),
                BenchmarkDtoState::Ready,
            ),
            [
                new BenchmarkInterCoolerDto(200, 2160, true, BenchmarkSpeedCategory::Fast),
                new BenchmarkInterCoolerDto(100, 2200, false, BenchmarkSpeedCategory::Slow),
                new BenchmarkInterCoolerDto(500, 3200, true, BenchmarkSpeedCategory::Fast),
            ],
            new DateTimeImmutable('2015-06-01 13:45:01.123456'),
            null,
            BenchmarkDtoState::Ready,
            'private-token',
        );
    }
}
