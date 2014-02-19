<?php

namespace LazyDataMapper\Tests\Caching;

use LazyDataMapper,
	LazyDataMapper\Tests,
	LazyDataMapper\Tests\HouseMapper,
	LazyDataMapper\Tests\KitchenMapper;

require_once __DIR__ . '/implementations/cache.php';
require_once __DIR__ . '/implementations/model/House.php';
require_once __DIR__ . '/implementations/model/Kitchen.php';

class LoadWithDescendantTest extends LazyDataMapper\Tests\AcceptanceTestCase
{

	public function testFirstGet()
	{
		$requestKey = new LazyDataMapper\RequestKey;
		$cache = new Tests\Cache\SimpleCache;
		$serviceAccessor = new Tests\ServiceAccessor;
		$suggestorCache = new LazyDataMapper\SuggestorCache($cache, $requestKey, $serviceAccessor);
		$accessor = new LazyDataMapper\Accessor($suggestorCache, $serviceAccessor);
		$facade = new Tests\HouseFacade($accessor, $serviceAccessor);

		$house = $facade->getById(3);

		$this->assertEquals("King's road", $house->street);
		$this->assertInstanceOf('LazyDataMapper\Tests\Kitchen', $house->kitchen);
		$this->assertEquals(22, $house->kitchen->area);

		return $facade;
	}


	/**
	 * @depends testFirstGet
	 */
	public function testCaching(Tests\HouseFacade $facade)
	{
		$house = $facade->getById(4);

		$this->assertEquals('Oak', $house->street);
		$this->assertInstanceOf('LazyDataMapper\Tests\Kitchen', $house->kitchen);
		$this->assertEquals(54, $house->kitchen->area);

		// KitchenMapper cannot be called, everything solves HouseMapper
		$this->assertEquals(1, HouseMapper::$calledGetById);
		$this->assertEquals(0, KitchenMapper::$calledGetById);
	}
}
