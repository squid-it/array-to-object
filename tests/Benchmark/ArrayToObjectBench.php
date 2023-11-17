<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Benchmark;

use PhpBench\Benchmark\Metadata\Annotations\Iterations;
use PhpBench\Benchmark\Metadata\Annotations\Revs;
use PhpBench\Benchmark\Metadata\Annotations\Warmup;
use ReflectionException;
use SquidIT\Hydrator\ArrayToObject;
use SquidIT\Hydrator\Class\ClassInfoGenerator;
use SquidIT\Hydrator\Exceptions\AmbiguousTypeException;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Complete\CarComplete;

class ArrayToObjectBench
{
    /**
     * @Revs(3)
     *
     * @Iterations(3)
     *
     * @Warmup(1)
     *
     * @throws AmbiguousTypeException|ReflectionException
     */
    public function benchInstantiateInsideLoop(): void
    {
        $i    = 1000;
        $data = $this->getTestData();

        while ($i > 0) {
            $classInfoGenerator = new ClassInfoGenerator();
            $hydrator           = new ArrayToObject($classInfoGenerator);

            $hydrator->hydrate($data, CarComplete::class);

            $i--;
        }
    }

    /**
     * @Revs(3)
     *
     * @Iterations(3)
     *
     * @Warmup(1)
     *
     * @throws AmbiguousTypeException|ReflectionException
     */
    public function benchInstantiateOutsideLoopWithCache(): void
    {
        $i    = 1000;
        $data = $this->getTestData();

        $classInfoGenerator = new ClassInfoGenerator();
        $hydrator           = new ArrayToObject($classInfoGenerator);

        while ($i > 0) {
            $hydrator->hydrate($data, CarComplete::class);

            $i--;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function getTestData(): array
    {
        return [
            'color'           => 'black',
            'nrOfDoors'       => 4,
            'mileagePerLiter' => 16.3,
            'passengerList'   => ['melvin', 'bert'],
            'manufacturer'    => [
                'addressLine1' => 'Beautiful Street 123',
                'addressLine2' => 'Apartment 1234',
                'city'         => 'Rotterdam',
                'employeeList' => [
                    ['employeeName' => 'cecil'],
                    ['employeeName' => 'melvin'],
                ],
            ],
            'interCoolers' => [
                [
                    'speedRangeMinRpm' => 200,
                    'speedRangeMaxRpm' => 2160,
                    'isWaterCooled'    => true,
                    'speedCategory'    => 'fast',
                ],
                [
                    'speedRangeMinRpm' => 100,
                    'speedRangeMaxRpm' => 2200,
                    'isWaterCooled'    => false,
                    'speedCategory'    => 'slow',
                ],
            ],
            'countryEntryDate' => '2015-06-01 13:45:01',
            'extraInfo'        => null,
        ];
    }
}
