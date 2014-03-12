<?php

namespace LazyDataMapper\Tests\Get;

use LazyDataMapper,
	LazyDataMapper\Tests,
	LazyDataMapper\Tests\xCarMapper;

require_once __DIR__ . '/implementations/cache.php';
require_once __DIR__ . '/implementations/model/xCar.php';

class GroupedParamMapTest extends LazyDataMapper\Tests\AcceptanceTestCase
{

	public function testGet()
	{
		$requestKey = new LazyDataMapper\RequestKey;
		$cache = new Tests\Cache\SimpleCache;
		$serviceAccessor = new Tests\ServiceAccessor;
		$suggestorCache = new LazyDataMapper\SuggestorCache($cache, $requestKey, $serviceAccessor);
		$accessor = new LazyDataMapper\Accessor($suggestorCache, $serviceAccessor);
		$facade = new Tests\xCarFacade($accessor, $serviceAccessor);

		$car = $facade->getById(1);

		$this->assertEquals('Seat', $car->brand);
		$this->assertEquals(['brand'], xCarMapper::$lastSuggestor->getParamNames('feature'));
		$this->assertEquals([], xCarMapper::$lastSuggestor->getParamNames('engine'));
		$this->assertEquals(1.8, $car->volume);
		$this->assertEquals(['volume'], xCarMapper::$lastSuggestor->getParamNames('engine'));
		$this->assertEquals([], xCarMapper::$lastSuggestor->getParamNames('feature'));
	}
}
