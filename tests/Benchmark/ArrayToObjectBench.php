<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Benchmark;

use JsonException;
use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use ReflectionException;
use SquidIT\Hydrator\ArrayToObject;
use SquidIT\Hydrator\Class\ClassInfoGenerator;
use SquidIT\Hydrator\DtoToObject;
use SquidIT\Hydrator\Exceptions\AmbiguousTypeException;
use SquidIT\Hydrator\Tests\Unit\ExampleArrays\CarData;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Complete\CarComplete;
use Throwable;

class ArrayToObjectBench
{
    /** @var array<string, mixed> */
    private array $carDataArray;
    private object $carDataObject;

    /**
     * @throws JsonException
     */
    public function setUp(): void
    {
        $this->carDataObject = json_decode(
            json_encode(CarData::regularArray(), JSON_THROW_ON_ERROR),
            false,
            512,
            JSON_THROW_ON_ERROR
        );

        $this->carDataArray = json_decode(
            json_encode(CarData::regularArray(), JSON_THROW_ON_ERROR),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }

    /**
     * @throws Throwable
     */
    #[Revs(3), Iterations(3), Warmup(1)]
    public function benchHydrateArrayDataUsingInstantiatedHydratorInsideLoop(): void
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
     * @throws Throwable
     */
    #[Revs(3), Iterations(3), Warmup(1)]
    public function benchHydrateArrayDataUsingInstantiatedHydratorOutsideLoopWithCache(): void
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
     * @throws Throwable
     */
    #[BeforeMethods('setUp'), Revs(3), Iterations(3), Warmup(1)]
    public function benchHydrateArrayDataFromJsonUsingInstantiatedHydratorOutsideLoopWithCache(): void
    {
        $i    = 1000;
        $data = $this->carDataArray;

        $classInfoGenerator = new ClassInfoGenerator();
        $hydrator           = new ArrayToObject($classInfoGenerator);

        while ($i > 0) {
            $hydrator->hydrate($data, CarComplete::class);

            $i--;
        }
    }

    /**
     * @throws Throwable
     */
    #[BeforeMethods('setUp'), Revs(3), Iterations(3), Warmup(1)]
    public function benchHydrateObjectDataFromJsonUsingInstantiatedHydratorOutsideLoopWithCache(): void
    {
        $i    = 1000;
        $data = $this->carDataObject;

        $classInfoGenerator = new ClassInfoGenerator();
        $hydrator           = new DtoToObject($classInfoGenerator);

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
