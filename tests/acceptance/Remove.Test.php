<?php

namespace LazyDataMapper\Tests\Remove;

use LazyDataMapper,
	LazyDataMapper\Tests;

require_once __DIR__ . '/implementations/cache.php';
require_once __DIR__ . '/implementations/model/Icebox.php';

class Test extends LazyDataMapper\Tests\AcceptanceTestCase
{

	/** @var Tests\IceboxFacade */
	private $facade;

	protected function setUp()
	{
		parent::setUp();

		$requestKey = new LazyDataMapper\RequestKey;
		$cache = new Tests\Cache\SimpleCache;
		$serviceAccessor = new Tests\ServiceAccessor;
		$suggestorCache = new LazyDataMapper\SuggestorCache($cache, $requestKey, $serviceAccessor);
		$accessor = new LazyDataMapper\Accessor($suggestorCache, $serviceAccessor);
		$this->facade = new Tests\IceboxFacade($accessor, $serviceAccessor);
	}


	public function testRemove()
	{
		$this->facade->remove(5);

		$this->assertNull($this->facade->getById(5));
		$this->assertInstanceOf('LazyDataMapper\Tests\Icebox', $this->facade->getById(4));
	}


	public function testRemoveByIds()
	{
		$this->facade->removeByIdsRange([5, 8]);

		$this->assertNull($this->facade->getById(5));
		$this->assertNull($this->facade->getById(8));
		$this->assertInstanceOf('LazyDataMapper\Tests\Icebox', $this->facade->getById(4));
	}


	public function testRemoveByRestrictions()
	{
		$restrictor = new Tests\IceboxRestrictor;
		$restrictor->limitCapacity(15, 30);
		$this->facade->removeByRestrictions($restrictor);

		$this->assertNull($this->facade->getById(4));
		$this->assertNull($this->facade->getById(5));
		$this->assertInstanceOf('LazyDataMapper\Tests\Icebox', $this->facade->getById(2));
		$this->assertInstanceOf('LazyDataMapper\Tests\Icebox', $this->facade->getById(8));
	}
}
