<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Unit\ExampleArrays;

use JsonException;

class CarData
{
    /**
     * @return array<string, mixed>
     */
    public static function regularArray(): array
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

    /**
     * @throws JsonException
     */
    public static function regularObject(): object
    {
        /* @phpstan-ignore return.type */
        return json_decode(
            json_encode(self::regularArray(), JSON_THROW_ON_ERROR),
            false,
            512,
            JSON_THROW_ON_ERROR
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function dottedJavascript(): array
    {
        return [
            'color'                                     => 'black',
            'nrOfDoors'                                 => 4,
            'mileagePerLiter'                           => 16.3,
            'passengerList'                             => ['melvin', 'bert'],
            'manufacturer.addressLine1'                 => 'Beautiful Street 123',
            'manufacturer.addressLine2'                 => 'Apartment 1234',
            'manufacturer.city'                         => 'Rotterdam',
            'manufacturer.employeeList[0].employeeName' => 'cecil',
            'manufacturer.employeeList[1].employeeName' => 'melvin',
            'interCoolers[0].speedRangeMinRpm'          => 200,
            'interCoolers[0].speedRangeMaxRpm'          => 2160,
            'interCoolers[0].isWaterCooled'             => true,
            'interCoolers[0].speedCategory'             => 'fast',
            'interCoolers[1].speedRangeMinRpm'          => 100,
            'interCoolers[1].speedRangeMaxRpm'          => 2200,
            'interCoolers[1].isWaterCooled'             => false,
            'interCoolers[1].speedCategory'             => 'slow',
            'countryEntryDate'                          => '2015-06-01 13:45:01',
            'extraInfo'                                 => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function dottedExplode(): array
    {
        return [
            'color'                                    => 'black',
            'nrOfDoors'                                => 4,
            'mileagePerLiter'                          => 16.3,
            'passengerList'                            => ['melvin', 'bert'],
            'manufacturer.addressLine1'                => 'Beautiful Street 123',
            'manufacturer.addressLine2'                => 'Apartment 1234',
            'manufacturer.city'                        => 'Rotterdam',
            'manufacturer.employeeList.0.employeeName' => 'cecil',
            'manufacturer.employeeList.1.employeeName' => 'melvin',
            'interCoolers.0.speedRangeMinRpm'          => 200,
            'interCoolers.0.speedRangeMaxRpm'          => 2160,
            'interCoolers.0.isWaterCooled'             => true,
            'interCoolers.0.speedCategory'             => 'fast',
            'interCoolers.1.speedRangeMinRpm'          => 100,
            'interCoolers.1.speedRangeMaxRpm'          => 2200,
            'interCoolers.1.isWaterCooled'             => false,
            'interCoolers.1.speedCategory'             => 'slow',
            'countryEntryDate'                         => '2015-06-01 13:45:01',
            'extraInfo'                                => null,
        ];
    }
}
