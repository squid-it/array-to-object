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

var_dump($carComplete);

```

### output
```
object(SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Complete\CarComplete)#6 (9) {
  ["color"]=>
  string(5) "black"
  ["nrOfDoors"]=>
  int(4)
  ["mileagePerLiter"]=>
  float(16.3)
  ["passengerList"]=>
  array(2) {
    [0]=>
    string(6) "melvin"
    [1]=>
    string(4) "bert"
  }
  ["manufacturer"]=>
  object(SquidIT\Hydrator\Tests\Unit\ExampleObjects\Manufacturer\Honda)#21 (4) {
    ["addressLine1"]=>
    string(20) "Beautiful Street 123"
    ["addressLine2"]=>
    string(14) "Apartment 1234"
    ["city"]=>
    string(9) "Rotterdam"
    ["employeeList"]=>
    array(2) {
      [0]=>
      object(SquidIT\Hydrator\Tests\Unit\ExampleObjects\Manufacturer\Employee)#24 (1) {
        ["employeeName"]=>
        string(5) "cecil"
      }
      [1]=>
      object(SquidIT\Hydrator\Tests\Unit\ExampleObjects\Manufacturer\Employee)#10 (1) {
        ["employeeName"]=>
        string(6) "melvin"
      }
    }
  }
  ["interCoolers"]=>
  array(2) {
    [0]=>
    object(SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Parts\InterCooler)#14 (4) {
      ["speedRangeMinRpm"]=>
      int(200)
      ["speedRangeMaxRpm"]=>
      int(2160)
      ["isWaterCooled"]=>
      bool(true)
      ["speedCategory"]=>
      enum(SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Speed::FAST)
    }
    [1]=>
    object(SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Parts\InterCooler)#44 (4) {
      ["speedRangeMinRpm"]=>
      int(100)
      ["speedRangeMaxRpm"]=>
      int(2200)
      ["isWaterCooled"]=>
      bool(false)
      ["speedCategory"]=>
      enum(SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Speed::SLOW)
    }
  }
  ["countryEntryDate"]=>
  object(DateTimeImmutable)#39 (3) {
    ["date"]=>
    string(26) "2015-06-01 13:45:01.000000"
    ["timezone_type"]=>
    int(3)
    ["timezone"]=>
    string(3) "UTC"
  }
  ["extraInfo"]=>
  NULL
  ["isInsured"]=>
  bool(true)
}
```
