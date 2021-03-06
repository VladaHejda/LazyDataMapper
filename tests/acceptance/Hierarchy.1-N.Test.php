<?php

namespace LazyDataMapper\Tests\Hierarchy;

use LazyDataMapper,
	LazyDataMapper\Tests,
	LazyDataMapper\Tests\SuggestorCache,
	LazyDataMapper\Tests\CarMapper,
	LazyDataMapper\Tests\DriverMapper;

require_once __DIR__ . '/implementations/model/Car.php';
require_once __DIR__ . '/implementations/model/Driver.php';

class OneManyTest extends LazyDataMapper\Tests\AcceptanceTestCase
{

	public function testFirstGet()
	{
		$requestKey = new LazyDataMapper\RequestKey;
		$cache = new Tests\Cache\SimpleCache;
		$serviceAccessor = new Tests\ServiceAccessor;
		$suggestorCache = new SuggestorCache($cache, $requestKey, $serviceAccessor);
		$accessor = new LazyDataMapper\Accessor($suggestorCache, $serviceAccessor);
		$facade = new Tests\DriverFacade($accessor, $serviceAccessor);

		$driver = $facade->getById(2);

		$this->assertEquals(9, $driver->wins);
		$this->assertEquals('FORD Mustang', $driver->cars[1]->title);

		$this->assertEquals(2, SuggestorCache::$calledGetCached);
		$this->assertEquals(3, SuggestorCache::$calledCacheSuggestion);
		$this->assertEquals(1, SuggestorCache::$calledCacheChild);

		return [$cache, $facade];
	}


	/**
	 * @depends testFirstGet
	 */
	public function testCaching(array $services)
	{
		list($cache, $facade) = $services;

		$driver = $facade->getById(5);

		$this->assertEquals('SKODA Yeti', $driver->cars[1]->title);

		$this->assertEquals(1, DriverMapper::$calledGetById);
		$this->assertEquals(0, CarMapper::$calledGetById);
		$this->assertEquals(0, CarMapper::$calledGetByRestrictions);

		$this->assertEquals(2, SuggestorCache::$calledGetCached);
		$this->assertEquals(0, SuggestorCache::$calledCacheSuggestion);
		$this->assertEquals(0, SuggestorCache::$calledCacheChild);

		$this->assertCount(2, $cache->cache);
	}
}
