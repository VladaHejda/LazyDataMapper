<?php

namespace LazyDataMapper\Tests\Hierarchy;

use LazyDataMapper,
	LazyDataMapper\Tests,
	LazyDataMapper\Tests\CarMapper;

require_once __DIR__ . '/implementations/model/Car.php';

class ModifyingTest extends LazyDataMapper\Tests\AcceptanceTestCase
{

	/** @var Tests\CarFacade */
	private static $facade;


	public function testSet()
	{
		$requestKey = new LazyDataMapper\RequestKey;
		$cache = new Tests\Cache\SimpleCache;
		$serviceAccessor = new Tests\ServiceAccessor;
		$suggestorCache = new LazyDataMapper\SuggestorCache($cache, $requestKey, $serviceAccessor);
		$accessor = new LazyDataMapper\Accessor($suggestorCache, $serviceAccessor);
		$facade = new Tests\CarFacade($accessor, $serviceAccessor);
		self::$facade = $facade;

		$car = $facade->getById(4);

		$car->price = 30000;
		$this->assertEquals(['price'], CarMapper::$lastSuggestor->getSuggestions());

		$this->assertEquals('Diablo', $car->name);
		$car->name = 'Urraco';
		$this->assertEquals(30000, $car->price);
		$this->assertEquals('Urraco', $car->name);
		$this->assertTrue($car->isChanged());
		$this->assertTrue($car->isChanged('name'));
		$this->assertFalse($car->isChanged('engine'));
		$this->assertEquals('Diablo', $car->getOriginal('name'));
		$this->assertEquals(['name' => 'Urraco', 'price' => 30000], $car->getChanges());

		$this->assertEquals(2, CarMapper::$calledGetById);

		return $car;
	}


	/**
	 * @depends testSet
	 */
	public function testUnset(Tests\Car $car)
	{
		unset($car->price);
		$this->assertEquals(0, $car->price);

		return $car;
	}


	/**
	 * @depends testUnset
	 */
	public function testReset(Tests\Car $car)
	{
		$car->reset('price');
		$car->reset('name');
		$this->assertEquals('Diablo', $car->name);
		$this->assertFalse($car->isChanged());
		$this->assertFalse($car->isChanged('name'));
		$this->assertEquals('Diablo', $car->getOriginal('name'));
		$this->assertEquals([], $car->getChanges());

		$car->name = 'Miura';
		$car->price = 25500;
		$car->reset('price');
		$this->assertTrue($car->isChanged());

		$car->price = 30000;
		$car->reset();
		$this->assertFalse($car->isChanged());
		$this->assertFalse($car->isChanged('name'));
		$this->assertFalse($car->isChanged('price'));

		$this->assertEquals(0, CarMapper::$calledGetById);

		return $car;
	}


	/**
	 * @depends testReset
	 */
	public function testUnwrapper(Tests\Car $car)
	{
		$this->assertEquals('Diablo', $car->name);
		$this->assertEquals('LAMBORGHINI Diablo', $car->title);
		$car->title = 'Seat Toledo';
		$this->assertEquals('Toledo', $car->name);
		$this->assertEquals('SEAT Toledo', $car->title);

		$this->assertEquals(1, CarMapper::$calledGetById);

		$this->assertEquals(480, $car->cylinderVolume);
		$this->assertEquals(5760, $car->volume);
		$this->assertEquals(12, $car->cylinders);
		$car->cylinderVolume = 220;
		$this->assertEquals(2640, $car->volume);
		$this->assertEquals(12, $car->cylinders);
		$this->assertEquals(220, $car->cylinderVolume);

		$this->assertEquals(2, CarMapper::$calledGetById);

		return $car;
	}


	/**
	 * @depends testUnwrapper
	 */
	public function testSetPrivate(Tests\Car $car)
	{
		$car->reset();
		$this->assertFalse($car->repaired);
		$car->addRepair();
		$this->assertTrue($car->repaired);

		$this->assertEquals(1, CarMapper::$calledGetById);

		$this->assertTrue($car->isChanged());
		$this->assertTrue($car->isChanged('repairs'));
		$this->assertEquals(0, $car->getOriginal('repairs'));
		$this->assertEquals(['repairs' => 1], $car->getChanges());

		return $car;
	}


	/**
	 * @depends testSetPrivate
	 */
	public function testSetReadonly(Tests\Car $car)
	{
		$car->vendor = 'Seat';
		$this->assertEquals('SEAT', $car->brand);

		return $car;
	}


	/**
	 * @depends testSetReadonly
	 */
	public function testSave(Tests\Car $car)
	{
		$car->reset();
		$car->name = 'Miura';
		$car->save();

		$this->assertEquals(['name' => 'Miura'], CarMapper::$lastHolder->getData());

		$this->assertEquals('Miura', $car->name);
		$this->assertEquals(211100, $car->price);
		$this->assertFalse($car->isChanged());
		$this->assertFalse($car->isChanged('name'));
		$this->assertFalse($car->isChanged('price'));
		$this->assertEquals('Miura', $car->getOriginal('name'));
		$this->assertEquals([], $car->getChanges());

		$car->reset();
		$this->assertEquals('Miura', $car->name);
	}


	/**
	 * @depends testSave
	 */
	public function testSuccessfullySaved()
	{
		$car = self::$facade->getById(4);
		$this->assertEquals('Miura', $car->name);

		$this->assertEquals(1, CarMapper::$calledGetById);

		return $car;
	}


	/**
	 * @depends testSet
	 */
	public function testImmutable(Tests\Car $car)
	{
		// undeclared
		$this->assertException(
			function() use ($car) { $car->undeclared = ''; },
			'LazyDataMapper\EntityException', LazyDataMapper\EntityException::WRITE_UNDECLARED
		);

		// private
		$this->assertException(
			function() use ($car) { $car->repairs = ''; },
			'LazyDataMapper\EntityException', LazyDataMapper\EntityException::WRITE_UNDECLARED
		);

		// readonly
		$this->assertException(
			function() use ($car) { $car->brand = ''; },
			'LazyDataMapper\EntityException', LazyDataMapper\EntityException::WRITE_READONLY
		);
	}
}
