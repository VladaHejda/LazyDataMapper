<?php

namespace LazyDataMapper\Tests\Entity;

use LazyDataMapper,
	LazyDataMapper\Tests,
	LazyDataMapper\Tests\CarMapper;


class CreateTest extends LazyDataMapper\Tests\AcceptanceTestCase
{

	protected function createServices()
	{
		$requestKey = new LazyDataMapper\RequestKey;
		$cache = new Tests\Cache\SimpleCache;
		$serviceAccessor = new Tests\ServiceAccessor;
		$suggestorCache = new LazyDataMapper\SuggestorCache($cache, $requestKey, $serviceAccessor);
		$accessor = new LazyDataMapper\Accessor($suggestorCache, $serviceAccessor);
		$facade = new Tests\CarFacade($accessor, $serviceAccessor);
		return [$cache, $facade];
	}


	public function testCreate()
	{
		list(, $facade) = $this->createServices();

		$car = $facade->create('Suzuki', 'Swift', 25000);
		$this->assertInstanceOf('LazyDataMapper\Tests\Car', $car);
		$car = $facade->getById($car->getId());
		$this->assertInstanceOf('LazyDataMapper\Tests\Car', $car);

		$this->assertEquals(0, CarMapper::$calledGetById);
	}


	public function testCreateWithData()
	{
		list($cache, $facade) = $this->createServices();

		$car = $facade->create('Ford', 'Mondeo', 35000);

		$this->assertInstanceOf('LazyDataMapper\Tests\Car', $car);
		$this->assertEquals('Mondeo', $car->name);
		$this->assertEquals(35000, $car->price);
		$this->assertFalse($car->repaired);
		$this->assertEquals(0, $car->engine);
		$this->assertEquals(0, $car->cylinders);

		$this->assertEquals(2, CarMapper::$calledGetById);
		$cached = reset($cache->cache);
		$this->assertEquals(['repairs', 'engine'], reset($cached));

		return [$cache, $facade];
	}


	/**
	 * @depends testCreateWithData
	 */
	public function testCaching(array $services)
	{
		list($cache, $facade) = $services;

		$car = $facade->create('Peugeot', '307', 41000);

		$this->assertEquals('307', $car->name);
		$this->assertEquals(41000, $car->price);
		$this->assertEquals(0, $car->engine);
		$this->assertEquals(0, $car->cylinders);

		$this->assertEquals(1, CarMapper::$calledGetById);
	}
}
