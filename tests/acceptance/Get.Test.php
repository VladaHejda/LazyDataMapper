<?php

namespace LazyDataMapper\Tests\Get;

use LazyDataMapper,
	LazyDataMapper\Tests;

require_once __DIR__ . '/implementations/model/Car.php';

class Test extends LazyDataMapper\Tests\AcceptanceTestCase
{

	public function testGet()
	{
		$requestKey = new LazyDataMapper\RequestKey;
		$cache = new Tests\Cache\SimpleCache;
		$serviceAccessor = new Tests\ServiceAccessor;
		$suggestorCache = new LazyDataMapper\SuggestorCache($cache, $requestKey, $serviceAccessor);
		$accessor = new LazyDataMapper\Accessor($suggestorCache, $serviceAccessor);
		$facade = new Tests\CarFacade($accessor, $serviceAccessor);

		$this->assertNull($facade->getById(99));

		$car = $facade->getById(2);

		$this->assertEquals('Gallardo', $car->name);
		$this->assertEquals('Gallardo', $car->name);
		$this->assertEquals('LAMBORGHINI', $car->brand);
		$this->assertEquals('LAMBORGHINI Gallardo', $car->title);
		$this->assertSame(TRUE, $car->repaired);
		$this->assertSame(184000, $car->price);
		$this->assertSame(184000, $car->price);
		$this->assertSame(184000, $car->price());
		$this->assertSame(184000, $car->price('USD'));
		$this->assertSame(3680000, $car->price('CZK'));
		$this->assertEquals('coupe', $car->chassis);
		$this->assertEquals(5200, $car->volume);
		$this->assertEquals(10, $car->cylinders);
		$this->assertEquals(520, $car->cylinderVolume);

		$this->assertTrue($car->isReadOnly('brand'));
		$this->assertFalse($car->isReadOnly('name'));

		$this->assertTrue(isset($car->name));
		$this->assertTrue(isset($car->repaired));
		$this->assertFalse(isset($car->undeclared));
		$this->assertFalse(isset($car->repairs));

		return $car;
	}


	/**
	 * @depends testGet
	 */
	public function testUnobtainable(Tests\Car $car)
	{
		// undeclared
		$this->assertException(
			function() use ($car) { $car->undeclared; },
			'LazyDataMapper\EntityException', LazyDataMapper\EntityException::READ_UNDECLARED
		);

		// private
		$this->assertException(
			function() use ($car) { $car->repairs; },
			'LazyDataMapper\EntityException', LazyDataMapper\EntityException::READ_UNDECLARED
		);
	}
}
