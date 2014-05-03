<?php

namespace LazyDataMapper\Tests\EntityCollection;

use LazyDataMapper,
	LazyDataMapper\Tests;

require_once __DIR__ . '/implementations/model/Car.php';

class AllTest extends LazyDataMapper\Tests\AcceptanceTestCase
{

	public function testGetAll()
	{
		$requestKey = new LazyDataMapper\RequestKey;
		$cache = new Tests\Cache\SimpleCache;
		$serviceAccessor = new Tests\ServiceAccessor;
		$suggestorCache = new LazyDataMapper\SuggestorCache($cache, $requestKey, $serviceAccessor);
		$accessor = new LazyDataMapper\Accessor($suggestorCache, $serviceAccessor);
		$facade = new Tests\CarFacade($accessor, $serviceAccessor);

		$cars = $facade->getAll();

		$this->assertCount(7, $cars);
		$this->assertInstanceOf('LazyDataMapper\Tests\Car', $cars[0]);
		$this->assertInstanceOf('LazyDataMapper\Tests\Car', $cars[6]);
		$this->assertEquals('Z4', $cars[0]->name);
		$this->assertEquals('Gallardo', $cars[1]->name);
		$this->assertEquals('Yeti', $cars[6]->name);
	}
}
