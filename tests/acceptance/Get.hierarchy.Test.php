<?php

namespace LazyDataMapper\Tests\Get;

use LazyDataMapper,
	LazyDataMapper\Tests,
	LazyDataMapper\Tests\RaceMapper,
	LazyDataMapper\Tests\CarMapper,
	LazyDataMapper\Tests\DriverMapper;

require_once __DIR__ . '/implementations/model/Race.php';
require_once __DIR__ . '/implementations/model/Car.php';
require_once __DIR__ . '/implementations/model/Driver.php';

class HierarchyTest extends LazyDataMapper\Tests\AcceptanceTestCase
{

	public function testTree()
	{
		$requestKey = new LazyDataMapper\RequestKey;
		$cache = new Tests\Cache\SimpleCache;
		$serviceAccessor = new Tests\ServiceAccessor;
		$suggestorCache = new LazyDataMapper\SuggestorCache($cache, $requestKey, $serviceAccessor);
		$accessor = new LazyDataMapper\Accessor($suggestorCache, $serviceAccessor);
		$facade = new Tests\RaceFacade($accessor, $serviceAccessor);

		$race = $facade->getById(5);

		$this->assertEquals('Christine Dorian', $race->car->driver->name);

		return [$cache, $facade];
	}


	/**
	 * @depends testTree
	 */
	public function testCachingTree(array $services)
	{
		list($cache, $facade) = $services;

		$race = $facade->getById(2);

		$this->assertEquals('John Gilbert', $race->car->driver->name);

		$this->assertEquals(1, RaceMapper::$calledGetById);
		$this->assertEquals(0, CarMapper::$calledGetById);
		$this->assertEquals(0, DriverMapper::$calledGetById);

		$this->assertCount(3, $cache->cache);
	}


	public function testEntityUnderContainer()
	{
		$requestKey = new LazyDataMapper\RequestKey;
		$cache = new Tests\Cache\SimpleCache;
		$serviceAccessor = new Tests\ServiceAccessor;
		$suggestorCache = new LazyDataMapper\SuggestorCache($cache, $requestKey, $serviceAccessor);
		$accessor = new LazyDataMapper\Accessor($suggestorCache, $serviceAccessor);
		$facade = new Tests\RaceFacade($accessor, $serviceAccessor);

		$race = $facade->getByIdsRange([2, 4]);

		$this->assertEquals('Mustang', $race[0]->car->name);

		return [$cache, $facade];
	}


	/**
	 * @depends testEntityUnderContainer
	 */
	public function testCachingEntityUnderContainer(array $services)
	{
		list($cache, $facade) = $services;

		$race = $facade->getByIdsRange([2, 4]);

		$this->assertEquals('Gallardo', $race[1]->car->name);

		$this->assertEquals(0, DriverMapper::$calledGetById);
		$this->assertEquals(1, CarMapper::$calledGetById);
		$this->assertEquals(0, RaceMapper::$calledGetById);
		$this->assertEquals(0, DriverMapper::$calledGetByRestrictions);
		$this->assertEquals(0, CarMapper::$calledGetByRestrictions);
		$this->assertEquals(1, RaceMapper::$calledGetByRestrictions);

		$this->assertCount(2, $cache->cache);
	}


	public function testTreeReversed()
	{
		$requestKey = new LazyDataMapper\RequestKey;
		$cache = new Tests\Cache\SimpleCache;
		$serviceAccessor = new Tests\ServiceAccessor;
		$suggestorCache = new LazyDataMapper\SuggestorCache($cache, $requestKey, $serviceAccessor);
		$accessor = new LazyDataMapper\Accessor($suggestorCache, $serviceAccessor);
		$facade = new Tests\DriverFacade($accessor, $serviceAccessor);

		$driver = $facade->getById(2);

		$this->assertEquals('Iowa', $driver->cars[1]->races[1]->country);

		return [$cache, $facade];
	}


	/**
	 * @depends testTreeReversed
	 */
	public function testCachingTreeReversed(array $services)
	{
		list($cache, $facade) = $services;

		$driver = $facade->getById(5);

		$this->assertEquals('Oregon', $driver->cars[1]->races[0]->country);

		$this->assertEquals(0, DriverMapper::$calledGetById);
		$this->assertEquals(0, CarMapper::$calledGetById);
		$this->assertEquals(0, RaceMapper::$calledGetById);
		$this->assertEquals(0, DriverMapper::$calledGetByRestrictions);
		$this->assertEquals(0, CarMapper::$calledGetByRestrictions);
		$this->assertEquals(1, RaceMapper::$calledGetByRestrictions);

		$this->assertCount(2, $cache->cache);
	}
}
