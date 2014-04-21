<?php

namespace LazyDataMapper\Tests\Entity;

use LazyDataMapper,
	LazyDataMapper\Tests,
	LazyDataMapper\Tests\SuggestorCache,
	LazyDataMapper\Tests\CarMapper;

require_once __DIR__ . '/implementations/model/Car.php';

class ByRestrictionsTest extends LazyDataMapper\Tests\AcceptanceTestCase
{

	public function testGetOne()
	{
		$requestKey = new LazyDataMapper\RequestKey;
		$cache = new Tests\Cache\SimpleCache;
		$serviceAccessor = new Tests\ServiceAccessor;
		$suggestorCache = new SuggestorCache($cache, $requestKey, $serviceAccessor);
		$accessor = new LazyDataMapper\Accessor($suggestorCache, $serviceAccessor);
		$facade = new Tests\CarFacade($accessor, $serviceAccessor);

		$restrictor = new Tests\CarRestrictor;
		$restrictor->limitCarName('Skoda', 'Yeti');
		$car = $facade->getOneByRestrictions($restrictor);

		$this->assertInstanceOf('LazyDataMapper\Tests\Car', $car);
		$this->assertEquals(7, $car->getId());
		$this->assertEquals('SKODA Yeti', $car->title);
		$this->assertEquals(10500, $car->price);

		$this->assertEquals(0, CarMapper::$calledGetByRestrictions);
		$this->assertEquals(3, CarMapper::$calledGetById);
		$this->assertCount(1, $cache->cache);
		$cached = reset($cache->cache);
		$this->assertEquals(['brand', 'name', 'price'], reset($cached));
		$this->assertEquals(1, SuggestorCache::$calledGetCached);
		$this->assertEquals(3, SuggestorCache::$calledCacheSuggestion);

		return $facade;
	}


	/**
	 * @depends testGetOne
	 */
	public function testCaching(Tests\CarFacade $facade)
	{
		$restrictor = new Tests\CarRestrictor;
		$restrictor->limitCarName('BMW', 'Z4');
		$car = $facade->getOneByRestrictions($restrictor);

		$this->assertInstanceOf('LazyDataMapper\Tests\Car', $car);
		$this->assertEquals(1, $car->getId());
		$this->assertEquals('BMW Z4', $car->title);
		$this->assertEquals(16250, $car->price);

		$this->assertEquals(1, CarMapper::$calledGetById);
		$this->assertEquals(1, SuggestorCache::$calledGetCached);
		$this->assertEquals(0, SuggestorCache::$calledCacheSuggestion);
		$this->assertEquals(0, SuggestorCache::$calledCacheChild);

		$this->assertEquals(['brand', 'name', 'price'], CarMapper::$lastSuggestor->getSuggestions());
	}


	/**
	 * @depends testGetOne
	 * @expectedException LazyDataMapper\Exception
	 */
	public function testWeakRestrictions(Tests\CarFacade $facade)
	{
		$restrictor = new Tests\CarRestrictor;
		$restrictor->limitPrice(150000);
		$facade->getOneByRestrictions($restrictor);
	}
}
