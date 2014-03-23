<?php

namespace LazyDataMapper\Tests\Caching;

use LazyDataMapper,
	LazyDataMapper\Tests,
	LazyDataMapper\Tests\SuggestorCache,
	LazyDataMapper\Tests\CarMapper,
	LazyDataMapper\Tests\DriverMapper;

require_once __DIR__ . '/implementations/model/Driver.php';
require_once __DIR__ . '/implementations/model/Car.php';

class ChildrenTest extends LazyDataMapper\Tests\AcceptanceTestCase
{

	public function testFirstGet()
	{
		$requestKey = new LazyDataMapper\RequestKey;
		$cache = new Tests\Cache\SimpleCache;
		$serviceAccessor = new Tests\ServiceAccessor;
		$suggestorCache = new SuggestorCache($cache, $requestKey, $serviceAccessor);
		$accessor = new LazyDataMapper\Accessor($suggestorCache, $serviceAccessor);
		$facade = new Tests\CarFacade($accessor, $serviceAccessor);

		$car = $facade->getById(1);

		$this->assertInstanceOf('LazyDataMapper\Tests\Driver', $car->driver);
		$this->assertEquals(16250, $car->price);
		$this->assertEquals('John', $car->driver->first_name);

		$this->assertEquals(2, SuggestorCache::$calledGetCached);
		$this->assertEquals(3, SuggestorCache::$calledCacheParamName);
		$this->assertEquals(1, SuggestorCache::$calledCacheChild);

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

		$this->assertEquals(1, CarMapper::$calledGetById);
		$this->assertEquals(1, DriverMapper::$calledGetById);

		$this->assertEquals(2, SuggestorCache::$calledGetCached);
		$this->assertEquals(0, SuggestorCache::$calledCacheParamName);
		$this->assertEquals(0, SuggestorCache::$calledCacheChild);

		$this->assertEquals(['driver', 'price'], CarMapper::$lastSuggestor->getSuggestions());
		$this->assertTrue(CarMapper::$lastSuggestor->hasChildren());
		$child = CarMapper::$lastSuggestor->getChild('driver');
		$this->assertInstanceOf('LazyDataMapper\Suggestor', $child);
		$this->assertEquals(['first_name'], $child->getSuggestions());
	}
}
