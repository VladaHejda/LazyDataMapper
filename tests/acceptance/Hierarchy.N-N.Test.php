<?php

namespace LazyDataMapper\Tests\Hierarchy;

use LazyDataMapper,
	LazyDataMapper\Tests,
	LazyDataMapper\Tests\CarMapper,
	LazyDataMapper\Tests\DriverMapper;

require_once __DIR__ . '/implementations/model/Car.php';
require_once __DIR__ . '/implementations/model/Driver.php';

class ManyManyTest extends LazyDataMapper\Tests\AcceptanceTestCase
{

	public function testManyToMany()
	{
		$requestKey = new LazyDataMapper\RequestKey;
		$cache = new Tests\Cache\SimpleCache;
		$serviceAccessor = new Tests\ServiceAccessor;
		$suggestorCache = new LazyDataMapper\SuggestorCache($cache, $requestKey, $serviceAccessor);
		$accessor = new LazyDataMapper\Accessor($suggestorCache, $serviceAccessor);
		$facade = new Tests\DriverFacade($accessor, $serviceAccessor);

		$drivers = $facade->getByIdsRange([4, 5]);

		$this->assertCount(0, $drivers[0]->famousCars);
		$this->assertEquals('Celica', $drivers[1]->famousCars[0]->name);
		$this->assertFalse(isset($drivers[1]->famousCars[1]));

		return $facade;
	}


	/**
	 * @depends testManyToMany
	 */
	public function testCachingManyToMany(Tests\DriverFacade $facade)
	{
		$drivers = $facade->getByIdsRange([1, 2, 3]);

		$this->assertEquals('Diablo', $drivers[0]->famousCars[0]->name);
		$this->assertEquals('Celica', $drivers[0]->famousCars[1]->name);
		$this->assertFalse(isset($drivers[0]->famousCars[2]));
		$this->assertEquals('Z4', $drivers[1]->famousCars[0]->name);
		$this->assertEquals('Gallardo', $drivers[1]->famousCars[1]->name);
		$this->assertFalse(isset($drivers[1]->famousCars[2]));
		$this->assertEquals('Z4', $drivers[2]->famousCars[0]->name);
		$this->assertEquals('Celica', $drivers[2]->famousCars[1]->name);
		$this->assertEquals('Yeti', $drivers[2]->famousCars[2]->name);
		$this->assertFalse(isset($drivers[2]->famousCars[3]));

		$this->assertEquals(0, DriverMapper::$calledGetById);
		$this->assertEquals(1, DriverMapper::$calledGetByRestrictions);
		$this->assertEquals(0, CarMapper::$calledGetById);
		$this->assertEquals(0, CarMapper::$calledGetByRestrictions);

		$this->assertTrue(DriverMapper::$lastHolder->hasLoadedChildren());
		$this->assertNotNull(DriverMapper::$lastHolder->famousCars);
		$data = [
			4 => ['name' => 'Diablo'],
			5 => ['name' => 'Celica'],
			1 => ['name' => 'Z4'],
			2 => ['name' => 'Gallardo'],
			7 => ['name' => 'Yeti'],
		];
		$this->assertEquals($data, DriverMapper::$lastHolder->famousCars->getData());
		$relations = [
			1 => [4, 5],
			2 => [1, 2],
			3 => [1, 5, 7],
		];
		$this->assertEquals($relations, DriverMapper::$lastHolder->famousCars->getRelations());
	}
}
