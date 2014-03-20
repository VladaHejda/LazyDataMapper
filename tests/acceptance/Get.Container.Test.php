<?php

namespace LazyDataMapper\Tests\GetContainer;

use LazyDataMapper,
	LazyDataMapper\Tests;

require_once __DIR__ . '/implementations/model/Car.php';

class Test extends LazyDataMapper\Tests\AcceptanceTestCase
{

	public function testGet()
	{
		$requestKey = new LazyDataMapper\RequestKey;
		$cache = new Tests\Cache\SimpleCache;
		$serviceAccessor = new Tests\ServiceAccessor;
		$suggestorCache = new LazyDataMapper\SuggestorCache($cache, $requestKey, $serviceAccessor);
		$accessor = new LazyDataMapper\Accessor($suggestorCache, $serviceAccessor);
		$facade = new Tests\CarFacade($accessor, $serviceAccessor);

		$cars = $facade->getByIdsRange([2, 5, 7]);

		$this->assertInstanceOf('LazyDataMapper\Tests\Car', $cars[0]);
		$this->assertInstanceOf('LazyDataMapper\Tests\Car', $cars[1]);
		$this->assertInstanceOf('LazyDataMapper\Tests\Car', $cars[2]);

		$this->assertEquals('Gallardo', $cars[0]->name);
		$this->assertEquals('Celica', $cars[1]->name);
		$this->assertEquals('Yeti', $cars[2]->name);

		$expected = [
			2 => 5200,
			5 => 1998,
			7 => 2000,
		];

		foreach ($cars as $car) {
			$this->assertInstanceOf('LazyDataMapper\Tests\Car', $car);
			$this->assertEquals($expected[$car->getId()], $car->volume);
		}

		$this->assertEquals(207240, $cars->price);
	}
}
