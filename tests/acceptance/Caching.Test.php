<?php

namespace LazyDataMapper\Tests\Caching;

use LazyDataMapper,
	LazyDataMapper\Tests,
	LazyDataMapper\Tests\CarMapper;

require_once __DIR__ . '/implementations/cache.php';
require_once __DIR__ . '/implementations/model/Car.php';

class Test extends LazyDataMapper\Tests\AcceptanceTestCase
{

	/** @var Tests\CarFacade */
	private static $facade;


	public function testFirstGet()
	{
		$requestKey = new LazyDataMapper\RequestKey;
		$cache = new Tests\Cache\SimpleCache;
		$serviceAccessor = new Tests\ServiceAccessor;
		$suggestorCache = new LazyDataMapper\SuggestorCache($cache, $requestKey, $serviceAccessor);
		$accessor = new LazyDataMapper\Accessor($suggestorCache, $serviceAccessor);
		$facade = new Tests\CarFacade($accessor, $serviceAccessor);
		self::$facade = $facade;

		$car = $facade->getById(6);

		$this->assertEquals('R8', $car->name);
		$this->assertEquals(['name'], CarMapper::$lastSuggestor->getParamNames());

		$this->assertEquals(5320, $car->engine);
		$this->assertEquals(['engine'], CarMapper::$lastSuggestor->getParamNames());

		// checks if getById is not called again
		$this->assertEquals('R8', $car->name);
		$this->assertEquals(2, CarMapper::$calledGetById);

		// tests if suggestions cached
		$this->assertCount(1, $cache->cache);
		$cached = reset($cache->cache);
		$this->assertEquals(['name', 'engine'], reset($cached));

		return [$cache, $facade];
	}


	/**
	 * @depends testFirstGet
	 */
	public function testCaching(array $services)
	{
		list($cache, $facade) = $services;

		$car = $facade->getById(5);

		$this->assertEquals('Celica', $car->name);
		$this->assertEquals(1998, $car->engine);

		// tests if getById called only once with right suggestions
		$this->assertEquals(1, CarMapper::$calledGetById);
		$this->assertEquals(['name', 'engine'], CarMapper::$lastSuggestor->getParamNames());

		// tries get new data
		$this->assertEquals(12740, $car->price);
		$this->assertEquals(2, CarMapper::$calledGetById);
		$this->assertEquals(['price'], CarMapper::$lastSuggestor->getParamNames());

		// tests if new suggestion cached
		$cached = reset($cache->cache);
		$this->assertEquals(['name', 'engine', 'price'], reset($cached));
	}


	public function testGetNonexistent()
	{
		$this->assertNull(self::$facade->getById(99));
		$this->assertEquals(0, CarMapper::$calledGetById);
	}
}
