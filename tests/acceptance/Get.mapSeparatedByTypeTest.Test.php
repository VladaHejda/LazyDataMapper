<?php

namespace Shelter\Tests\Get;

use Shelter,
	Shelter\Tests,
	Shelter\Tests\CarMapper;

require_once __DIR__ . '/implementations/cache.php';
require_once __DIR__ . '/implementations/model/Car.php';

class MapSeparatedByTypeTest extends Shelter\Tests\TestCase
{

	public function testGet()
	{
		$requestKey = new Shelter\RequestKey;
		$cache = new Tests\Cache\SimpleCache;
		$serviceAccessor = new Tests\ServiceAccessor;
		$suggestorCache = new Shelter\SuggestorCache($cache, $requestKey, $serviceAccessor);
		$accessor = new Shelter\Accessor($suggestorCache, $serviceAccessor);
		$facade = new Tests\CarFacade($accessor, $serviceAccessor);

		$car = $facade->getById(1);

		$this->assertEquals('Seat', $car->brand);
		$this->assertEquals(['brand'], CarMapper::$lastSuggestor->getParamNames('feature'));
		$this->assertEquals([], CarMapper::$lastSuggestor->getParamNames('engine'));
		$this->assertEquals(1.8, $car->volume);
		$this->assertEquals(['volume'], CarMapper::$lastSuggestor->getParamNames('engine'));
		$this->assertEquals([], CarMapper::$lastSuggestor->getParamNames('feature'));
	}
}
