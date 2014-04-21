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

class OneMany_OneManyTest extends LazyDataMapper\Tests\AcceptanceTestCase
{

	public function testTreeReversed()
	{
		$requestKey = new LazyDataMapper\RequestKey;
		$cache = new Tests\Cache\SimpleCache;
		$serviceAccessor = new Tests\ServiceAccessor;
		$suggestorCache = new LazyDataMapper\SuggestorCache($cache, $requestKey, $serviceAccessor);
		$accessor = new LazyDataMapper\Accessor($suggestorCache, $serviceAccessor);
		$facade = new Tests\DriverFacade($accessor, $serviceAccessor);

		$driver = $facade->getById(2);

		$this->assertEquals('John Gilbert', $driver->name);
		$this->assertEquals(5320, $driver->cars[2]->engine);
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

		$this->assertEquals('Christine Dorian', $driver->name);
		$this->assertEquals(1998, $driver->cars[0]->engine);
		$this->assertEquals(2000, $driver->cars[1]->engine);
		$this->assertFalse(isset($driver->cars[2]));
		$this->assertCount(0, $driver->cars[0]->races);
		$this->assertEquals('Oregon', $driver->cars[1]->races[0]->country);
		$this->assertEquals('Buffalo', $driver->cars[1]->races[1]->country);
		$this->assertFalse(isset($driver->cars[1]->races[2]));

		$this->assertEquals(1, DriverMapper::$calledGetById);
		$this->assertEquals(0, CarMapper::$calledGetById);
		$this->assertEquals(0, RaceMapper::$calledGetById);
		$this->assertEquals(0, DriverMapper::$calledGetByRestrictions);
		$this->assertEquals(0, CarMapper::$calledGetByRestrictions);
		$this->assertEquals(0, RaceMapper::$calledGetByRestrictions);

		$this->assertCount(3, $cache->cache);
	}
}
