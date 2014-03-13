<?php

namespace LazyDataMapper\Tests\Caching;

use LazyDataMapper,
	LazyDataMapper\Tests,
	LazyDataMapper\Tests\CarMapper;

require_once __DIR__ . '/implementations/cache.php';
require_once __DIR__ . '/implementations/model/Car.php';

class ContainerTest extends LazyDataMapper\Tests\AcceptanceTestCase
{

	public function testFirstGet()
	{
		$requestKey = new LazyDataMapper\RequestKey;
		$cache = new Tests\Cache\SimpleCache;
		$serviceAccessor = new Tests\ServiceAccessor;
		$suggestorCache = new LazyDataMapper\SuggestorCache($cache, $requestKey, $serviceAccessor);
		$accessor = new LazyDataMapper\Accessor($suggestorCache, $serviceAccessor);
		$facade = new Tests\CarFacade($accessor, $serviceAccessor);

		$cars = $facade->getByIdsRange([4, 6]);

		$this->assertEquals('Diablo', $cars[0]->name);
		$this->assertEquals('R8', $cars[1]->name);
		$this->assertEquals(5760, $cars[0]->engine);
		$this->assertEquals(5320, $cars[1]->engine);

		// checks calls count
		$this->assertEquals(0, CarMapper::$calledGetByRestrictions);
		$this->assertEquals(4, CarMapper::$calledGetById);

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

		$cars = $facade->getByIdsRange([5, 4]);

		$this->assertEquals('Celica', $cars[0]->name);
		$this->assertEquals('Diablo', $cars[1]->name);
		$this->assertEquals(1998, $cars[0]->engine);
		$this->assertEquals(5760, $cars[1]->engine);

		// tests if getById called only once with right suggestions
		$this->assertEquals(1, CarMapper::$calledGetByRestrictions);
		$this->assertEquals(0, CarMapper::$calledGetById);

		// tries get new data
		$this->assertTrue($cars[0]->repaired);

		// tests if new suggestion cached
		$cached = reset($cache->cache);
		$this->assertEquals(['name', 'engine', 'repairs'], reset($cached));
	}
}
