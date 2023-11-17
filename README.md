# Object Hydrator

Create object from array data using class typed properties to map data to object

### usage - manual:
```php
<?php

declare(strict_types=1);

use SquidIT\Hydrator\ArrayToObject;
use SquidIT\Hydrator\Class\ClassInfoGenerator;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Complete\CarComplete;

$classInfoGenerator = new ClassInfoGenerator();
$hydrator           = new ArrayToObject($classInfoGenerator);

$data = [
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

$carComplete = $hydrator->hydrate($data, CarComplete::class);

```
