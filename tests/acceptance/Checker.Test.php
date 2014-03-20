<?php

namespace LazyDataMapper\Tests\Checker;

use LazyDataMapper,
	LazyDataMapper\Tests;

require_once __DIR__ . '/implementations/model/Car.php';

class Test extends LazyDataMapper\Tests\AcceptanceTestCase
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


	public function testSave()
	{
		$car = $this->facade->getById(1);

		// required parameter
		$car->name = '';
		$this->assertException(
			function() use ($car) { $car->save(); },
			'LazyDataMapper\IntegrityException'
		);

		// check method
		$car->reset();
		$car->vendor = 'Very long vendor name';
		$this->assertException(
			function() use ($car) { $car->save(); },
			'LazyDataMapper\IntegrityException', NULL, 'Brand is too long!'
		);

		// multi-message Exception
		$car->reset();
		$car->name = '';
		$car->vendor = 'Very long vendor name';
		try {
			$car->save(FALSE);
			$this->fail('Expected that IntegrityException was thrown.');
		} catch (LazyDataMapper\IntegrityException $e) {
			$this->assertCount(2, $e->getAllMessages());
		}

		// throw first
		$car->reset();
		$car->name = '';
		$car->vendor = 'Very long vendor name';
		try {
			$car->save(TRUE);
			$this->fail('Expected that IntegrityException was thrown.');
		} catch (LazyDataMapper\IntegrityException $e) {
			$this->assertCount(1, $e->getAllMessages());
		}
	}


	public function testCreate()
	{
		// should pass normally
		$this->facade->create('Suzuki', '', 3000);

		try {
			$this->facade->create('Very long vendor name', '', 1000);
			$this->fail('Expected that IntegrityException was thrown.');
		} catch (LazyDataMapper\IntegrityException $e) {
			$this->assertCount(1, $e->getAllMessages());
		}

		try {
			$this->facade->create('Very long vendor name', '', 1000, FALSE);
			$this->fail('Expected that IntegrityException was thrown.');
		} catch (LazyDataMapper\IntegrityException $e) {
			$this->assertCount(2, $e->getAllMessages());
		}
	}
}
