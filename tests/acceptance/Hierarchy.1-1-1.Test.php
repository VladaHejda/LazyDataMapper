<?php

namespace LazyDataMapper\Tests\Hierarchy;

use LazyDataMapper,
	LazyDataMapper\Tests,
	LazyDataMapper\Tests\RaceMapper,
	LazyDataMapper\Tests\CarMapper,
	LazyDataMapper\Tests\DriverMapper;

require_once __DIR__ . '/implementations/model/Race.php';
require_once __DIR__ . '/implementations/model/Car.php';
require_once __DIR__ . '/implementations/model/Driver.php';

class OneOneOneTest extends LazyDataMapper\Tests\AcceptanceTestCase
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
}
