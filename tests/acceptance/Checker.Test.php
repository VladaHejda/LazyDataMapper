<?php

namespace LazyDataMapper\Tests\Checker;

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
		$requestKey = new LazyDataMapper\RequestKey;
		$cache = new Tests\Cache\SimpleCache;
		$serviceAccessor = new Tests\ServiceAccessor;
		$suggestorCache = new LazyDataMapper\SuggestorCache($cache, $requestKey, $serviceAccessor);
		$accessor = new LazyDataMapper\Accessor($suggestorCache, $serviceAccessor);
		$this->facade = new Tests\IceboxFacade($accessor, $serviceAccessor);
	}


	public function testSave()
	{
		$icebox = $this->facade->getById(2);

		// required parameter
		$icebox->color = '';
		$this->assertException(
			function() use ($icebox) { $icebox->save(); },
			'LazyDataMapper\IntegrityException'
		);

		// check method
		$icebox->reset();
		$icebox->addFood('apple');
		$icebox->addFood('carrot');
		$icebox->capacity = 15;
		$this->assertException(
			function() use ($icebox) { $icebox->save(); },
			'LazyDataMapper\IntegrityException'
		);

		// MultiException
		$icebox->reset();
		$icebox->color = '';
		$icebox->addFood('apple');
		$icebox->addFood('carrot');
		$icebox->capacity = 15;
		try {
			$icebox->save();
			$this->fail('Expected that IntegrityException was thrown.');
		} catch (LazyDataMapper\IntegrityException $e) {
			$this->assertCount(2, $e->getAllMessages());
		}

		// throw first
		$icebox->reset();
		$icebox->color = '';
		$icebox->addFood('apple');
		$icebox->addFood('carrot');
		$icebox->capacity = 15;
		try {
			$icebox->save(TRUE);
			$this->fail('Expected that IntegrityException was thrown.');
		} catch (LazyDataMapper\IntegrityException $e) {
			$this->assertCount(1, $e->getAllMessages());
		}
	}


	public function testCreate()
	{
		// should pass normally
		$this->facade->create(['color' => '']);

		$data = [
			'color' => 'nice',
			'food' => ['salad', 'cucumber', 'rhubarb', 'cress', 'radish', ],
			'capacity' => 15,
		];

		try {
			$this->facade->create($data);
			$this->fail('Expected that IntegrityException was thrown.');
		} catch (LazyDataMapper\IntegrityException $e) {
			$this->assertCount(1, $e->getAllMessages());
		}

		try {
			$this->facade->create($data, FALSE);
			$this->fail('Expected that IntegrityException was thrown.');
		} catch (LazyDataMapper\IntegrityException $e) {
			$this->assertCount(2, $e->getAllMessages());
		}
	}
}
