<?php

namespace LazyDataMapper\Tests\Caching;

use LazyDataMapper,
	LazyDataMapper\Tests,
	LazyDataMapper\Tests\CarMapper,
	LazyDataMapper\Tests\DriverMapper;

require_once __DIR__ . '/implementations/cache.php';
require_once __DIR__ . '/implementations/model/Driver.php';
require_once __DIR__ . '/implementations/model/Car.php';

class DescendantsTest extends LazyDataMapper\Tests\AcceptanceTestCase
{

	public function testFirstGet()
	{
		$requestKey = new LazyDataMapper\RequestKey;
		$cache = new Tests\Cache\SimpleCache;
		$serviceAccessor = new Tests\ServiceAccessor;
		$suggestorCache = new LazyDataMapper\SuggestorCache($cache, $requestKey, $serviceAccessor);
		$accessor = new LazyDataMapper\Accessor($suggestorCache, $serviceAccessor);
		$facade = new Tests\CarFacade($accessor, $serviceAccessor);

		$car = $facade->getById(1);

		$this->assertInstanceOf('LazyDataMapper\Tests\Driver', $car->driver);
		$this->assertEquals(16250, $car->price);

		$this->assertEquals('John', $car->driver->first_name);

		return $facade;
	}


	/**
	 * @depends testFirstGet
	 */
	public function testCaching(Tests\CarFacade $facade)
	{
		$car = $facade->getById(2);

		$this->assertInstanceOf('LazyDataMapper\Tests\Driver', $car->driver);
		$this->assertEquals(184000, $car->price);
		$this->assertEquals('Mike', $car->driver->first_name);

		// tests counts of getById calls
		$this->assertEquals(1, CarMapper::$calledGetById);
		$this->assertEquals(1, DriverMapper::$calledGetById);

		// tests suggestions
		$this->assertEquals(['driver', 'price'], CarMapper::$lastSuggestor->getParamNames());
		$this->assertTrue(CarMapper::$lastSuggestor->hasDescendants());
		$this->assertTrue(CarMapper::$lastSuggestor->hasDescendant('LazyDataMapper\Tests\Driver', $source));
		$this->assertEquals('driver', $source);
		$descendant = CarMapper::$lastSuggestor->getDescendant('LazyDataMapper\Tests\Driver', $source);
		$this->assertInstanceOf('LazyDataMapper\ISuggestor', $descendant);
		$this->assertEquals(['first_name'], $descendant->getParamNames());
	}
}
