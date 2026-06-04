# Object Hydrator

Hydrate typed objects from associative arrays, dotted arrays, or DTO-like source objects by mapping input keys or
property names to corresponding typed target properties. The library also includes a helper base class for
DTO-to-JSON output.

The supplied keys or source-object property names must match the names of the target object properties.

## Installation

### v3.* (PHP 8.4+)
```bash
composer require squidit/array-to-object:^3.0
```

### v2.* (PHP 8.2 / 8.3)
```bash
composer require squidit/array-to-object:^2.0
```

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

If you only need to convert dotted keys into a nested array, use `DotNotationToMultiDimensional` directly:

```php
use SquidIT\Hydrator\DotNotationToMultiDimensional;

$dotNotationToMultiDimensional = new DotNotationToMultiDimensional($dataDotNotationJavascript);
$nestedData                    = $dotNotationToMultiDimensional->convert();
```

Pass `DotNotationFormat::EXPLODE` as the second constructor argument when using explode-style dotted keys.

## Usage - example (DTO/object input):

When the source data already arrives as an object, use `DtoToObject`. The source-object property names must match the
target-object property names. Nested hydration and type casting work the same way as with array input.

```php
<?php

declare(strict_types=1);

use SquidIT\Hydrator\Class\ClassInfoGenerator;
use SquidIT\Hydrator\DtoToObject;

final class CarInputDto
{
    public function __construct(
        public string $color,
        public int $nrOfDoors,
    ) {}
}

final class CarSummary
{
    public function __construct(
        public string $color,
        public int $nrOfDoors,
    ) {}
}

$sourceDto = new CarInputDto('black', 4);
$hydrator  = new DtoToObject(new ClassInfoGenerator());

$carSummary = $hydrator->hydrate($sourceDto, CarSummary::class);
```

## Hydrating multiple objects

`ArrayToObject`, `DotNotationArrayToObject`, and `DtoToObject` all provide `hydrateMulti()` for indexed lists of
source records:

```php
$arrayHydrator = new ArrayToObject(new ClassInfoGenerator());
$dtoHydrator   = new DtoToObject(new ClassInfoGenerator());

$carCompleteList = $arrayHydrator->hydrateMulti([$data, $data], CarComplete::class);
$carSummaryList  = $dtoHydrator->hydrateMulti([$sourceDto, $sourceDto], CarSummary::class);
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

## Default values

When input omits a property, the hydrator uses the target object's default value when one exists. Defaults can come
from constructor-promoted properties or regular declared properties.

```php
final class Car
{
    public function __construct(
        public string $color,
        public bool $isInsured = true,
    ) {}
}
```

Object defaults are cloned during hydration, so each hydrated object receives its own default object instance instead
of sharing mutable state with other hydrated objects.

## Hydration lifecycle

Target objects are created without executing the constructor body, then their typed properties are assigned directly.
This means reflected default values are honored, but constructor side effects and constructor-only validation logic do
not run during hydration. Use `ObjectValidatorInterface` for checks that must happen after hydration.

## Object validation
If an object needs validation after hydration, implement `SquidIT\Hydrator\Interface\ObjectValidatorInterface`.

The hydrator calls `validate()` after all properties have been hydrated. When validation fails, throw a
`\SquidIT\Hydrator\Exceptions\ValidationFailureException`. The supplied `PathTracker` can be used to include the
property path in the exception message, including nested object and array positions.

```php
use SquidIT\Hydrator\Exceptions\ValidationFailureException;
use SquidIT\Hydrator\Interface\ObjectValidatorInterface;
use SquidIT\Hydrator\Property\PathTracker;

class CarWithCustomEngine implements ObjectValidatorInterface
{
    public function __construct(
        public int $engineDisplacementInCc,
    ) {}

    /**
     * @throws ValidationFailureException
     */
    public function validate(PathTracker $pathTracker): void
    {
        if ($this->engineDisplacementInCc < 600 || $this->engineDisplacementInCc > 8000) {
            $propertyPath = $pathTracker->getPath('engineDisplacementInCc');

            throw new ValidationFailureException(
                sprintf('Invalid value received for property: %s, value needs to be between 600 and 8000', $propertyPath)
            );
        }
    }
}
```

## User-safe error messages

By default, hydration errors include implementation detail that is useful for developers. If the message may reach an
end user, enable safe error messages when creating the hydrator:

```php
use SquidIT\Hydrator\ArrayToObject;
use SquidIT\Hydrator\Class\ClassInfoGenerator;
use SquidIT\Hydrator\DotNotationArrayToObject;
use SquidIT\Hydrator\DtoToObject;
use SquidIT\Hydrator\Property\DotNotationFormat;

$arrayHydrator       = new ArrayToObject(new ClassInfoGenerator(), true);
$dtoHydrator         = new DtoToObject(new ClassInfoGenerator(), true);
$dotNotationHydrator = new DotNotationArrayToObject(
    new ClassInfoGenerator(),
    DotNotationFormat::JAVASCRIPT,
    true,
);
```

With safe messages enabled, a missing nested value is reported using a user-facing property path, for example:
`Path: manufacturer.employeeList[0].employeeName - no data supplied for required property`.

## DTO to JSON output

When a DTO needs predictable JSON output, extend `SquidIT\Hydrator\Abstract\AbstractObjectToDto`.
If the DTO class must be declared `readonly`, extend
`SquidIT\Hydrator\Abstract\AbstractReadOnlyObjectToDto` instead. PHP only allows a readonly class to extend another
readonly class, and both abstract classes provide the same `toArray()` and `jsonSerialize()` behavior.
These abstract classes are specifically intended to help DTOs prepare JSON-friendly output through PHP's
`JsonSerializable` flow.

Public and protected properties are included automatically. `DateTimeImmutable` values are formatted as
`Y-m-d\TH:i:s.u`, backed enums are converted to their scalar values, and private properties are excluded.
Use `toArray()` when you need a deep nested array without embedded DTO objects; `jsonSerialize()` delegates
to the same conversion.

```php
<?php

declare(strict_types=1);

use DateTimeImmutable;
use SquidIT\Hydrator\Abstract\AbstractObjectToDto;

final class CarDto extends AbstractObjectToDto
{
    public function __construct(
        public string $color,
        public DateTimeImmutable $createdAt,
    ) {}
}

$carDto = new CarDto('black', new DateTimeImmutable('2026-05-18 12:34:56.123456'));

echo json_encode($carDto, JSON_THROW_ON_ERROR);
// {"color":"black","createdAt":"2026-05-18T12:34:56.123456"}

$carDto->toArray();
// ['color' => 'black', 'createdAt' => '2026-05-18T12:34:56.123456']
```

Readonly DTOs use the readonly base class:

```php
use DateTimeImmutable;
use SquidIT\Hydrator\Abstract\AbstractReadOnlyObjectToDto;

final readonly class ReadOnlyCarDto extends AbstractReadOnlyObjectToDto
{
    public function __construct(
        public string $color,
        public DateTimeImmutable $createdAt,
    ) {}
}
```

If a DTO needs a different JSON date format, override the protected format constant:

```php
final class PublicCarDto extends AbstractObjectToDto
{
    protected const string DATE_TIME_FORMAT = 'Y-m-d';
}
```

## Type casting/juggling array values into object properties
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

## Upgrading

### Update v1.* => V2.*
Interface change

Adjust all references:
* From: \SquidIT\Hydrator\ArrayToObjectHydratorInterface
* To: \SquidIT\Hydrator\Interface\ArrayToObjectHydratorInterface

### Update v2.* => v3.*
* Drops support for PHP 8.2 and 8.3, requires PHP 8.4+.
* Hydration hot path was reworked. Cached/warm hydration benchmarks are roughly 30% faster than v2, and ~40% faster than v2 prior to its mutation-removal patch. No public API changes; existing v2 code keeps working on PHP 8.4+.
