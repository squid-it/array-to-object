# Object Hydrator

Create an object from array data by mapping provided array keys to corresponding typed class property names.

The array keys must match the names of the object properties.

## Usage - example (multi dimensional array):
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

## Usage - example (array dot notation):
```php
<?php

declare(strict_types=1);

use SquidIT\Hydrator\Class\ClassInfoGenerator;
use SquidIT\Hydrator\DotNotationArrayToObject;
use SquidIT\Hydrator\Property\DotNotationFormat;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Complete\CarComplete;

$classInfoGenerator = new ClassInfoGenerator();
$hydratorJavaScript = new DotNotationArrayToObject($classInfoGenerator, DotNotationFormat::JAVASCRIPT);
$hydratorExplode    = new DotNotationArrayToObject($classInfoGenerator, DotNotationFormat::EXPLODE);

$dataDotNotationJavascript = [
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

$dataDotNotationExplode = [
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

$carComplete = $hydratorJavaScript->hydrate($dataDotNotationJavascript, CarComplete::class);
$carComplete = $hydratorExplode->hydrate($dataDotNotationExplode, CarComplete::class);

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
  bool(true)   <--- object default property and was not present in our data array
}
```

## Nested objects
If a class property contains a nested object, the hydrator can infer the object type by reading the property type.

In the example below, the `Car::class` contains a named property `manufacturer` which is of type `Honda::class`.
When hydrating, we need to provide all data required to create a Honda object.

```php
class Car
{
    public function __construct(
        public string $color,
        public Honda $manufacturer,
    ) {}
}

$data = [
    'color'           => 'black',
    'manufacturer'    => [  // <-- Honda::class
        'name'         => 'Beautiful Street 124',
        'city'         => 'Rotterdam',
        'employeeList' => []
    ],
];
```

## Nested objects: Array of objects
If a class property contains an array of objects, we need to add a property attribute:  
`SquidIT\Hydrator\Attributes\ArrayOf([CLASSNAME])`.

In the example below, the `Honda::class` contains a property `employeeList` which should contain an array of 
`Employee::class` objects.  

By adding the property attribute `SquidIT\Hydrator\Attributes\ArrayOf(Employee::class)` our hydrator knows how to hydrate 
array data found under the 'employeeList' object property.

```php
use SquidIT\Hydrator\Attributes\ArrayOf;

class Honda implements ManufacturerInterface
{
    /**
     * @param array<int, Employee> $employeeList
     */
    public function __construct(
        public string $name,
        public string $city,
        #[ArrayOf(Employee::class)]
        public array $employeeList,
    ) {}
}
```

## Type casting/juggling array vales into object properties
It is important to note that the hydrator will only work on classes that only contain typed properties.
If a non typed property is found an `SquidIT\Hydrator\Exceptions\AmbiguousTypeException` exception will be thrown.

The hydrator supports casting into the following property types

#### int:
if a string contains only digits *(plus and minus signs are allowed)*

#### bool:
The following values will be cast to `true`
* `1` [int]
* `'true'` [string]
* `'1'` [string]
* `'y'` [string]
* `'yes'` [string]

The following values will be cast to `false`
* `0` [int]
* `'false'` [string]
* `'0'` [string]
* `'n'` [string]
* `'no'` [string]

#### DateTimeImmutable::class:
Any string value supported by `strtotime()`  
*please note: as author of this library I feel no need to support the DateTime::class*

#### BackedEnum:
Any integer of string backed enum value

#### UnionTypes:
:x: Union types are not supported because we are unable to infer concrete object type implementation.


### Update v1.* => V2.*
Interface change

Adjust all references:
* From: \SquidIT\Hydrator\ArrayToObjectHydratorInterface
* To: \SquidIT\Hydrator\Interface\ArrayToObjectHydratorInterface
