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

class ManyOneTest extends LazyDataMapper\Tests\AcceptanceTestCase
{

	public function testEntityUnderCollection()
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
	 * @depends testEntityUnderCollection
	 */
	public function testCachingEntityUnderCollection(array $services)
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
}
