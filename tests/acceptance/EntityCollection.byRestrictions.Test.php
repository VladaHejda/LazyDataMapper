<?php

namespace LazyDataMapper\Tests\EntityCollection;

use LazyDataMapper,
	LazyDataMapper\Tests;

require_once __DIR__ . '/implementations/model/Car.php';
require_once __DIR__ . '/implementations/model/Driver.php';

class ByRestrictionsTest extends LazyDataMapper\Tests\AcceptanceTestCase
{

	/** @var Tests\CarFacade */
	private $facade;


	protected function setUp()
	{
		$requestKey = new LazyDataMapper\RequestKey;
		$cache = new Tests\Cache\SimpleCache;
		$serviceAccessor = new Tests\ServiceAccessor;
		$suggestorCache = new LazyDataMapper\SuggestorCache($cache, $requestKey, $serviceAccessor);
		$accessor = new LazyDataMapper\Accessor($suggestorCache, $serviceAccessor);
		$this->facade = new Tests\CarFacade($accessor, $serviceAccessor);
	}


	public function testEquals()
	{
		$restrictor = new Tests\CarRestrictor;
		$restrictor->limitChassis(['roadster', 'sedan']);
		$cars = $this->facade->getByRestrictions($restrictor);
		$this->assertCount(3, $cars);
		$this->assertEquals(1, $cars[0]->getId());
		$this->assertEquals(5, $cars[1]->getId());
		$this->assertEquals(6, $cars[2]->getId());
	}


	public function testRange()
	{
		$restrictor = new Tests\CarRestrictor;
		$restrictor->limitPrice(12000, 15000);
		$cars = $this->facade->getByRestrictions($restrictor);
		$this->assertCount(1, $cars);
		$this->assertEquals(5, $cars[0]->getId());

		$restrictor = new Tests\CarRestrictor;
		$restrictor->limitPrice(180000);
		$cars = $this->facade->getByRestrictions($restrictor);
		$this->assertCount(2, $cars);
		$this->assertEquals(2, $cars[0]->getId());
		$this->assertEquals(4, $cars[1]->getId());

		$restrictor = new Tests\CarRestrictor;
		$restrictor->limitPrice(NULL, 16000);
		$cars = $this->facade->getByRestrictions($restrictor);
		$this->assertCount(2, $cars);
		$this->assertEquals(5, $cars[0]->getId());
		$this->assertEquals(7, $cars[1]->getId());

		return $restrictor;
	}


	public function testMatch()
	{
		$requestKey = new LazyDataMapper\RequestKey;
		$cache = new Tests\Cache\SimpleCache;
		$serviceAccessor = new Tests\ServiceAccessor;
		$suggestorCache = new LazyDataMapper\SuggestorCache($cache, $requestKey, $serviceAccessor);
		$accessor = new LazyDataMapper\Accessor($suggestorCache, $serviceAccessor);
		$driverFacade = new Tests\DriverFacade($accessor, $serviceAccessor);

		$restrictor = new Tests\DriverRestrictor;
		$cars = $this->facade->getByIdsRange([1, 4]);
		$restrictor->limitFamousCars($cars);
		$drivers = $driverFacade->getByRestrictions($restrictor);
		$this->assertCount(3, $drivers);
		$this->assertEquals(1, $drivers[0]->getId());
		$this->assertEquals(2, $drivers[1]->getId());
		$this->assertEquals(3, $drivers[2]->getId());

		$car = $this->facade->getById(5);
		$restrictor->limitFamousCars($car);
		$drivers = $driverFacade->getByRestrictions($restrictor);
		$this->assertCount(4, $drivers);
		$this->assertEquals(5, $drivers[3]->getId());
	}


	public function testNotEquals()
	{
		$restrictor = new Tests\CarRestrictor;
		$restrictor->limitChassis(['coupe', 'roadster'], TRUE);
		$cars = $this->facade->getByRestrictions($restrictor);
		$this->assertCount(2, $cars);
		$this->assertEquals(5, $cars[0]->getId());
		$this->assertEquals(7, $cars[1]->getId());
	}


	/**
	 * @depends testRange
	 */
	public function testLimitsAggregate(Tests\CarRestrictor $restrictor)
	{
		$restrictor->limitChassis('SUV');
		$cars = $this->facade->getByRestrictions($restrictor);
		$this->assertCount(1, $cars);
		$this->assertEquals(7, $cars[0]->getId());
	}
}
