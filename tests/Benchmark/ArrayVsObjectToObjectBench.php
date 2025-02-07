<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Benchmark;

use JsonException;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use ReflectionException;
use SquidIT\Hydrator\ArrayToObject;
use SquidIT\Hydrator\Class\ClassInfoGenerator;
use SquidIT\Hydrator\DtoToObject;
use SquidIT\Hydrator\Exceptions\AmbiguousTypeException;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Complete\CarComplete;

class ArrayVsObjectToObjectBench
{
    /**
     * @throws AmbiguousTypeException|ReflectionException
     * @throws JsonException
     */
    #[Revs(3), Iterations(3), Warmup(1)]
    public function benchHydrateArrayDataFromJsonStringDoubleJsonDecode(): void
    {
        $i    = 1;
        $data = $this->getTestData();

        /** @var array<string, mixed> $hydrationData */
        $hydrationData      = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
        $validationData     = json_decode($data, false, 512, JSON_THROW_ON_ERROR);
        $classInfoGenerator = new ClassInfoGenerator();
        $hydrator           = new ArrayToObject($classInfoGenerator);

        while ($i > 0) {
            $hydrator->hydrate($hydrationData, CarComplete::class);

            $i--;
        }
    }

    /**
     * @throws AmbiguousTypeException|ReflectionException
     * @throws JsonException
     */
    #[Revs(3), Iterations(3), Warmup(1)]
    public function benchHydrateArrayDataFromJsonStringSingleJsonDecode(): void
    {
        $i    = 1;
        $data = $this->getTestData();

        /** @var object $hydrationData */
        $hydrationData      = json_decode($data, false, 512, JSON_THROW_ON_ERROR);
        $classInfoGenerator = new ClassInfoGenerator();
        $hydrator           = new DtoToObject($classInfoGenerator);

        while ($i > 0) {
            $hydrator->hydrate($hydrationData, CarComplete::class);

            $i--;
        }
    }

    /**
     * @throws AmbiguousTypeException|ReflectionException
     * @throws JsonException
     */
    #[Revs(3), Iterations(3), Warmup(1)]
    public function benchHydrateArrayDataFromJsonStringDoubleSimdJsonDecode(): void
    {
        $i    = 1;
        $data = $this->getTestData();

        /** @var array<string, mixed> $hydrationData */
        $hydrationData      = simdjson_decode($data, true, 512);
        $validationData     = simdjson_decode($data, false, 512);
        $classInfoGenerator = new ClassInfoGenerator();
        $hydrator           = new ArrayToObject($classInfoGenerator);

        while ($i > 0) {
            $hydrator->hydrate($hydrationData, CarComplete::class);

            $i--;
        }
    }

    /**
     * @throws AmbiguousTypeException|ReflectionException
     * @throws JsonException
     */
    #[Revs(3), Iterations(3), Warmup(1)]
    public function benchHydrateArrayDataFromJsonStringSingleSimdJsonDecode(): void
    {
        $i    = 1;
        $data = $this->getTestData();

        /** @var object $hydrationData */
        $hydrationData      = simdjson_decode($data, false, 512);
        $classInfoGenerator = new ClassInfoGenerator();
        $hydrator           = new DtoToObject($classInfoGenerator);

        while ($i > 0) {
            $hydrator->hydrate($hydrationData, CarComplete::class);

            $i--;
        }
    }

    /**
     * @throws AmbiguousTypeException|ReflectionException
     * @throws JsonException
     */
    #[Revs(3), Iterations(3), Warmup(1)]
    public function benchHydrateArrayDataFromJsonStringSingleSimdJsonDecode(): void
    {
        $i    = 1;
        $data = $this->getTestData();

        /** @var array<string, mixed> $hydrationData */
        $hydrationData      = simdjson_decode($data, true, 512);
        $validationData     = simdjson_decode($data, false, 512);
        $classInfoGenerator = new ClassInfoGenerator();
        $hydrator           = new ArrayToObject($classInfoGenerator);

        while ($i > 0) {
            $hydrator->hydrate($hydrationData, CarComplete::class);

            $i--;
        }
    }

    private function getTestData(): string
    {
        return '{
            "color": "black",
            "nrOfDoors": 4,
            "mileagePerLiter": 16.3,
            "passengerList": [
                "melvin",
                "bert"
            ],
            "manufacturer": {
                "addressLine1": "Beautiful Street 123",
                "addressLine2": "Apartment 1234",
                "city": "Rotterdam",
                "employeeList": [
                    {
                        "employeeName": "cecil"
                    },
                    {
                        "employeeName": "melvin"
                    }
                ]
            },
            "interCoolers": [
                {
                    "speedRangeMinRpm": 200,
                    "speedRangeMaxRpm": 2160,
                    "isWaterCooled": true,
                    "speedCategory": "fast"
                },
                {
                    "speedRangeMinRpm": 100,
                    "speedRangeMaxRpm": 2200,
                    "isWaterCooled": false,
                    "speedCategory": "slow"
                }
            ],
            "countryEntryDate": "2015-06-01 13:45:01",
            "extraInfo": null
        }';
    }
}
