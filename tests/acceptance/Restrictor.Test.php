<?php

namespace LazyDataMapper\Tests\Restrictor;

use LazyDataMapper,
	LazyDataMapper\Tests;

require_once __DIR__ . '/implementations/cache.php';
require_once __DIR__ . '/implementations/model/Icebox.php';

/**
 * @internal changed name convention because of conflict with Restrictor unit test
 */
class AcceptanceTest extends LazyDataMapper\Tests\AcceptanceTestCase
{

	/** @var Tests\IceboxFacade */
	private $facade;


	protected function setUp()
	{
		$requestKey = new LazyDataMapper\RequestKey;
		$cache = new Tests\Cache\SimpleCache;
		$serviceAccessor = new Tests\ServiceAccessor;
		$suggestorCache = new LazyDataMapper\SuggestorCache($cache, $requestKey, $serviceAccessor);
		$accessor = new LazyDataMapper\Accessor($suggestorCache, $serviceAccessor);
		$this->facade = new Tests\IceboxFacade($accessor, $serviceAccessor);
	}


	public function testEquals()
	{
		$restrictor = new Tests\IceboxRestrictor;
		$restrictor->limitColor(['blue', 'white']);
		$iceboxes = $this->facade->getByRestrictions($restrictor);
		$this->assertCount(2, $iceboxes);
		$this->assertEquals(4, $iceboxes[0]->getId());
		$this->assertEquals(8, $iceboxes[1]->getId());
	}


	public function testRange()
	{
		$restrictor = new Tests\IceboxRestrictor;
		$restrictor->limitCapacity(15, 20);
		$iceboxes = $this->facade->getByRestrictions($restrictor);
		$this->assertCount(1, $iceboxes);
		$this->assertEquals(4, $iceboxes[0]->getId());

		$restrictor = new Tests\IceboxRestrictor;
		$restrictor->limitCapacity(25);
		$iceboxes = $this->facade->getByRestrictions($restrictor);
		$this->assertCount(2, $iceboxes);
		$this->assertEquals(2, $iceboxes[0]->getId());
		$this->assertEquals(5, $iceboxes[1]->getId());

		$restrictor = new Tests\IceboxRestrictor;
		$restrictor->limitCapacity(NULL, 20);
		$iceboxes = $this->facade->getByRestrictions($restrictor);
		$this->assertCount(2, $iceboxes);
		$this->assertEquals(4, $iceboxes[0]->getId());
		$this->assertEquals(8, $iceboxes[1]->getId());

		return $restrictor;
	}


	public function testMatch()
	{
		$restrictor = new Tests\IceboxRestrictor;
		$restrictor->limitFood('egg');
		$iceboxes = $this->facade->getByRestrictions($restrictor);
		$this->assertCount(2, $iceboxes);
		$this->assertEquals(2, $iceboxes[0]->getId());
		$this->assertEquals(4, $iceboxes[1]->getId());

		$restrictor->limitFood('jam');
		$iceboxes = $this->facade->getByRestrictions($restrictor);
		$this->assertCount(3, $iceboxes);
		$this->assertEquals(8, $iceboxes[2]->getId());
	}


	public function testNotEquals()
	{
		$restrictor = new Tests\IceboxRestrictor;
		$restrictor->limitColor(['black', 'white'], TRUE);
		$iceboxes = $this->facade->getByRestrictions($restrictor);
		$this->assertCount(2, $iceboxes);
		$this->assertEquals(5, $iceboxes[0]->getId());
		$this->assertEquals(8, $iceboxes[1]->getId());
	}


	/**
	 * @depends testRange
	 */
	public function testLimitsAggregate(Tests\IceboxRestrictor $restrictor)
	{
		$restrictor->limitColor('blue');
		$iceboxes = $this->facade->getByRestrictions($restrictor);
		$this->assertCount(1, $iceboxes);
		$this->assertEquals(8, $iceboxes[0]->getId());
	}
}
