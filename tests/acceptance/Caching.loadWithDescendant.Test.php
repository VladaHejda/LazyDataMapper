<?php

namespace Shelter\Tests\Caching;

use Shelter,
	Shelter\Tests,
	Shelter\Tests\HouseMapper,
	Shelter\Tests\KitchenMapper;

require_once __DIR__ . '/implementations/cache.php';
require_once __DIR__ . '/implementations/model/House.php';
require_once __DIR__ . '/implementations/model/Kitchen.php';

class LoadWithDescendantTest extends Shelter\Tests\TestCase
{

	public function testFirstGet()
	{
		$requestKey = new Shelter\RequestKey;
		$cache = new Tests\Cache\SimpleCache;
		$serviceAccessor = new Tests\ServiceAccessor;
		$suggestorCache = new Shelter\SuggestorCache($cache, $requestKey, $serviceAccessor);
		$accessor = new Shelter\Accessor($suggestorCache, $serviceAccessor);
		$facade = new Tests\HouseFacade($accessor, $serviceAccessor);

		$house = $facade->getById(3);

		$this->assertEquals("King's road", $house->street);
		$this->assertInstanceOf('Shelter\Tests\Kitchen', $house->kitchen);
		$this->assertEquals(22, $house->kitchen->area);

		return [$cache, $facade];
	}


	/**
	 * @depends testFirstGet
	 */
	public function testCaching(array $services)
	{
		list($cache, $facade) = $services;
		HouseMapper::$calledGetById = 0;
		KitchenMapper::$calledGetById = 0;

		// force cache
		foreach ($cache->cache as $originalKey => $cached) {
			$newKey = $originalKey;
			$newKey[strlen($newKey) -1] = 1;
			$cache->cache[$newKey] = $cached;
		}

		$house = $facade->getById(4);

		$this->assertEquals('Oak', $house->street);
		$this->assertInstanceOf('Shelter\Tests\Kitchen', $house->kitchen);
		$this->assertEquals(54, $house->kitchen->area);

		// KitchenMapper cannot be called, everything solves HouseMapper
		$this->assertEquals(1, HouseMapper::$calledGetById);
		$this->assertEquals(0, KitchenMapper::$calledGetById);
	}
}
