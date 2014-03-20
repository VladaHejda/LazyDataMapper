<?php

namespace LazyDataMapper\Tests\Get;

use LazyDataMapper,
	LazyDataMapper\Tests,
	LazyDataMapper\Tests\DriverMapper;

require_once __DIR__ . '/implementations/model/Driver.php';

class GroupedParamMapTest extends LazyDataMapper\Tests\AcceptanceTestCase
{

	public function testGet()
	{
		$requestKey = new LazyDataMapper\RequestKey;
		$cache = new Tests\Cache\SimpleCache;
		$serviceAccessor = new Tests\ServiceAccessor;
		$suggestorCache = new LazyDataMapper\SuggestorCache($cache, $requestKey, $serviceAccessor);
		$accessor = new LazyDataMapper\Accessor($suggestorCache, $serviceAccessor);
		$facade = new Tests\DriverFacade($accessor, $serviceAccessor);

		$driver = $facade->getById(1);

		$this->assertEquals('Pooh', $driver->last_name);
		$this->assertEquals(['last_name'], DriverMapper::$lastSuggestor->getParamNames('personal'));
		$this->assertEquals([], DriverMapper::$lastSuggestor->getParamNames('score'));
		$this->assertEquals(2, $driver->accidents);
		$this->assertEquals(['accidents'], DriverMapper::$lastSuggestor->getParamNames('score'));
		$this->assertEquals([], DriverMapper::$lastSuggestor->getParamNames('personal'));
	}
}
