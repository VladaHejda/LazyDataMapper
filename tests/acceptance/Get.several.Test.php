<?php

namespace LazyDataMapper\Tests\Get;

use LazyDataMapper,
	LazyDataMapper\Tests,
	LazyDataMapper\Tests\SuggestorCache,
	LazyDataMapper\Tests\CarMapper;

require_once __DIR__ . '/implementations/model/Car.php';

class SeveralTest extends LazyDataMapper\Tests\AcceptanceTestCase
{

	public function testFirstGet()
	{
		$requestKey = new LazyDataMapper\RequestKey;
		$cache = new Tests\Cache\SimpleCache;
		$serviceAccessor = new Tests\ServiceAccessor;
		$suggestorCache = new SuggestorCache($cache, $requestKey, $serviceAccessor);
		$accessor = new LazyDataMapper\Accessor($suggestorCache, $serviceAccessor);
		$facade = new Tests\CarFacade($accessor, $serviceAccessor);

		$this->assertEquals('AUDI',  $facade->getById(6)->brand);

		$this->assertEquals('TOYOTA', $facade->getByIdsRange([5])[0]->brand);

		$this->assertEquals(19990, $facade->getByIdsRange([1, 6])[1]->price);

		$this->assertEquals('Z4', $facade->getById(1)->name);

		return [$cache, $facade];
	}


	/**
	 * @depends testFirstGet
	 */
	public function testCaching(array $services)
	{
		list($cache, $facade) = $services;

		$this->assertEquals('FORD', $facade->getById(3)->brand);

		$this->assertEquals(1, CarMapper::$calledGetById);
		$this->assertEquals(0, CarMapper::$calledGetByRestrictions);

		$cars = $facade->getByIdsRange([4, 2]);
		$this->assertEquals('LAMBORGHINI', $cars[0]->brand);

		$this->assertEquals(1, CarMapper::$calledGetById);
		$this->assertEquals(1, CarMapper::$calledGetByRestrictions);

		$this->assertEquals(16250, $facade->getByIdsRange([1])[0]->price);

		$this->assertEquals(1, CarMapper::$calledGetById);
		$this->assertEquals(2, CarMapper::$calledGetByRestrictions);

		$car = $facade->getById(7);
		$this->assertEquals('Yeti', $car->name);

		$this->assertEquals(2, CarMapper::$calledGetById);
		$this->assertEquals(2, CarMapper::$calledGetByRestrictions);

		// entities must be independent each other
		$this->assertEquals('SKODA', $car->brand);
		$this->assertEquals('Diablo', $cars[0]->name);
		$this->assertEquals('Gallardo', $cars[1]->name);
		$this->assertEquals(184000, $cars[1]->price);

		$this->assertEquals(6, CarMapper::$calledGetById);
		$this->assertEquals(2, CarMapper::$calledGetByRestrictions);
		$this->assertCount(4, $cache->cache);

		$this->assertEquals(4, SuggestorCache::$calledGetCached);
		$this->assertEquals(4, SuggestorCache::$calledCacheSuggestion);
	}
}
