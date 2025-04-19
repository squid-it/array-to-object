<?php

declare(strict_types=1);

namespace SquidIT\Hydrator\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SquidIT\Hydrator\ArrayToObject;
use SquidIT\Hydrator\Class\ClassInfoGenerator;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Simple\CarWithDefaultDoors;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Car\Simple\CarWithDefaultDoorsInNonPromotedProperty;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Cinema\Movie;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Cinema\Title;
use SquidIT\Hydrator\Tests\Unit\ExampleObjects\Manufacturer\Ford;
use Throwable;

class ArrayToObjectDefaultValuesTest extends TestCase
{
    private ArrayToObject $arrayToObject;

    protected function setUp(): void
    {
        $classInfoGenerator  = new ClassInfoGenerator();
        $this->arrayToObject = new ArrayToObject($classInfoGenerator);
    }

    /**
     * @throws Throwable
     */
    public function testHydrateClassWithMissingPropertyDataUsesDefaultFromConstructor(): void
    {
        $data = [
            'color'           => 'red',
            'mileagePerLiter' => 26.1,
            'isInsured'       => true,
            'passengerList'   => ['cecil', 'melvin'],
            'manufacturer'    => new Ford(),
            'extraInfo'       => null,
        ];

        /** @var CarWithDefaultDoors $carWithDefaultNrOfDoors */
        $carWithDefaultNrOfDoors = $this->arrayToObject->hydrate($data, CarWithDefaultDoors::class);

        self::assertSame(3, $carWithDefaultNrOfDoors->nrOfDoors);
    }

    /**
     * @throws Throwable
     */
    public function testHydrateClassWithMissingPropertyDataUsesDefaultFromDefaultProperty(): void
    {
        $data = [
            'color'           => 'red',
            'mileagePerLiter' => 26.1,
            'isInsured'       => true,
            'passengerList'   => ['cecil', 'melvin'],
            'manufacturer'    => new Ford(),
            'extraInfo'       => null,
        ];

        /** @var CarWithDefaultDoorsInNonPromotedProperty $carWithDefaultDoorsInNonPromotedProperty */
        $carWithDefaultDoorsInNonPromotedProperty = $this->arrayToObject->hydrate($data, CarWithDefaultDoorsInNonPromotedProperty::class);

        self::assertSame(4, $carWithDefaultDoorsInNonPromotedProperty->nrOfDoors);
    }

    /**
     * @throws Throwable
     */
    public function testHydrateClassWithADefaultValueContainingANewObjectSucceeds(): void
    {
        $data = [
            'releaseDate' => '2025-04-19 18:00:00',
        ];

        $movie1 = $this->arrayToObject->hydrate($data, Movie::class);
        $movie2 = $this->arrayToObject->hydrate($data, Movie::class);

        self::assertSame(Title::DEFAULT_MOVIE_TITLE, $movie1->title->name);
        self::assertNotSame($movie1->title, $movie2->title);
    }
}
