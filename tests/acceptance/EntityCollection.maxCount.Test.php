<?php

namespace LazyDataMapper\Tests\EntityCollection;

use LazyDataMapper,
	LazyDataMapper\Tests;

require_once __DIR__ . '/implementations/model/Car.php';

class MaxCountTest extends LazyDataMapper\Tests\AcceptanceTestCase
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


	public function testMaxCountNotExceeded()
	{
		$restrictor = new Tests\CarRestrictor;
		$restrictor->limitChassis('coupe');
		$cars = $this->facade->getByRestrictions($restrictor, 5);
		$this->assertCount(3, $cars);
	}


	public function testMaxCountExceeded()
	{
		$restrictor = new Tests\CarRestrictor;
		$restrictor->limitChassis('coupe');
		$cars = $this->facade->getByRestrictions($restrictor, 2);
		$this->assertCount(2, $cars);
		$this->assertEquals('Gallardo', $cars[0]->name);
		$this->assertEquals('Mustang', $cars[1]->name);
	}


	public function testExceedanceDisallowed()
	{
		$restrictor = new Tests\CarRestrictor;
		$restrictor->limitChassis('coupe');
		$this->assertException(function() use ($restrictor) {
			$this->facade->getByRestrictions($restrictor, 2, LazyDataMapper\Facade::CANNOT_EXCEED);
		}, 'LazyDataMapper\TooManyItemsException');
	}


	public function testPaging()
	{
		$restrictor = new Tests\CarRestrictor;
		$restrictor->limitChassis('coupe');
		$cars = $this->facade->getByRestrictions($restrictor, 2, 2);
		$this->assertCount(1, $cars);
		$this->assertEquals('Diablo', $cars[0]->name);
	}


	public function testExceededInformation()
	{
		$restrictor = new Tests\CarRestrictor;
		$restrictor->limitChassis('coupe');
		$this->facade->getByRestrictions($restrictor, 5, 1, $exceeded);
		$this->assertFalse($exceeded);
		$this->facade->getByRestrictions($restrictor, 2, 1, $exceeded);
		$this->assertTrue($exceeded);
	}
}
