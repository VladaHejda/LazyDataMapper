<?php

namespace LazyDataMapper\Tests\Caching;

use LazyDataMapper,
	LazyDataMapper\Tests,
	LazyDataMapper\Tests\SuggestorCache,
	LazyDataMapper\Tests\RaceMapper,
	LazyDataMapper\Tests\CarMapper;

require_once __DIR__ . '/implementations/model/Race.php';
require_once __DIR__ . '/implementations/model/Car.php';

class LoadWithChildTest extends LazyDataMapper\Tests\AcceptanceTestCase
{

	public function testFirstGet()
	{
		$requestKey = new LazyDataMapper\RequestKey;
		$cache = new Tests\Cache\SimpleCache;
		$serviceAccessor = new Tests\ServiceAccessor;
		$suggestorCache = new SuggestorCache($cache, $requestKey, $serviceAccessor);
		$accessor = new LazyDataMapper\Accessor($suggestorCache, $serviceAccessor);
		$facade = new Tests\RaceFacade($accessor, $serviceAccessor);

		$race = $facade->getById(5);

		$this->assertEquals('Oregon', $race->country);
		$this->assertInstanceOf('LazyDataMapper\Tests\Car', $race->car);
		$this->assertEquals(10500, $race->car->price);

		$this->assertEquals(2, SuggestorCache::$calledGetCached);
		$this->assertEquals(3, SuggestorCache::$calledCacheParamName);
		$this->assertEquals(1, SuggestorCache::$calledCacheChild);

		return $facade;
	}


	/**
	 * @depends testFirstGet
	 */
	public function testCaching(Tests\RaceFacade $facade)
	{
		$race = $facade->getById(4);

		$this->assertEquals('Texas', $race->country);
		$this->assertInstanceOf('LazyDataMapper\Tests\Car', $race->car);
		$this->assertEquals(184000, $race->car->price);

		// CarMapper cannot be called, everything solves RaceMapper
		$this->assertEquals(1, RaceMapper::$calledGetById);
		$this->assertEquals(0, CarMapper::$calledGetById);

		$this->assertEquals(2, SuggestorCache::$calledGetCached);
		$this->assertEquals(0, SuggestorCache::$calledCacheParamName);
		$this->assertEquals(0, SuggestorCache::$calledCacheChild);
	}
}
