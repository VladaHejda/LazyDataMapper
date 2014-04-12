<?php

namespace LazyDataMapper\Tests;

use LazyDataMapper,
	LazyDataMapper\IntegrityException;

require_once __DIR__ . '/default.php';
require_once __DIR__ . '/serviceAccessor.php';

class Car extends LazyDataMapper\Entity
{

	protected $privateParams = ['repairs', 'chassis_id'];


	protected function getBrand($brand)
	{
		return strtoupper($brand);
	}


	protected function getTitle()
	{
		return  "$this->brand $this->name";
	}


	protected function getPrice($price, $currency = 'USD')
	{
		switch ($currency) {
			case 'USD':
				return (int) $price;
			case 'CZK':
				return (int) $price * 20;
		}
	}


	protected function getChassis()
	{
		switch ($this->getBase('chassis_id')) {
			case 0:
				return 'roadster';
			case 1:
				return 'coupe';
			case 2:
				return 'sedan';
			case 3:
				return 'SUV';
		}
	}


	protected function getEngine($engine)
	{
		$engine = explode('|', $engine);
		return (int) $engine[1];
	}


	protected function getVolume()
	{
		return $this->engine;
	}


	protected function getCylinders()
	{
		$engine = explode('|', $this->getBase('engine'));
		return (int) $engine[0];
	}


	protected function getCylinderVolume()
	{
		return $this->volume / $this->cylinders;
	}


	protected function getDriver()
	{
		return $this->getChild('LazyDataMapper\Tests\Driver');
	}


	protected function getRepaired()
	{
		return (bool) $this->getBase('repairs');
	}


	protected function getRaces()
	{
		$restrictor = new RaceRestrictor;
		$restrictor->limitCar($this);
		return $this->getChild('LazyDataMapper\Tests\Race', $restrictor);
	}


	// yes, I know, this is weird, but it is for testing purposes
	protected function getBestDriver()
	{
		return $this->getChild('LazyDataMapper\Tests\Driver', 5);
	}


	protected function setName($name)
	{
		return $name;
	}


	protected function setPrice($price)
	{
		if ($price < 0) {
			throw new IntegrityException;
		}
		return (int) $price;
	}


	protected function setTitle($title)
	{
		$separator = strpos($title, ' ');
		$brand = substr($title, 0, $separator);
		$name = substr($title, $separator +1);
		$this->setImmutable('brand', $brand);
		$this->name = $name;
	}


	protected function setDriver(Driver $driver)
	{
		// forbidden driver
		if ($driver->id == 3) {
			return NULL;
		}
		return $driver->id;
	}


	protected function setCylinderVolume($volume)
	{
		$engine = explode('|', $this->getBase('engine'));
		$volume = $volume  * $engine[0];
		$this->setImmutable('engine', "$engine[0]|$volume");
	}


	protected function setVendor($vendor)
	{
		$this->setImmutable('brand', $vendor);
	}


	public function addRepair()
	{
		$repairs = $this->getBase('repairs');
		$this->setImmutable('repairs', $repairs +1);
	}
}


class Cars extends LazyDataMapper\EntityContainer
{

	protected function getPrice()
	{
		$total = 0;
		foreach ($this->getParams('price') as $price) {
			$total += $price;
		}
		return $total;
	}
}


class CarFacade extends LazyDataMapper\Facade
{

	public function create($brand, $name, $price, $throwFirst = TRUE)
	{
		$public = ['name' => $name, 'price' => $price];
		$private = ['brand' => $brand];
		return $this->createEntity($public, $private, $throwFirst);
	}
}


class CarRestrictor extends LazyDataMapper\FilterRestrictor
{

	public function limitPrice($min, $max = NULL)
	{
		$this->inRange('price', $min, $max);
	}


	public function limitChassis($chassis, $deny = FALSE)
	{
		if (!is_array($chassis)) {
			$chassis = [$chassis];
		}

		foreach ($chassis as &$one) {
			switch ($one) {
				case 'roadster':
					$one = 0;
					break;
				case 'coupe':
					$one = 1;
					break;
				case 'sedan':
					$one = 2;
					break;
				case 'SUV':
					$one = 3;
					break;
			}
		}

		if ($deny) {
			$this->notEquals('chassis_id', $chassis);
		} else {
			$this->equals('chassis_id', $chassis);
		}
	}


	public function limitDriver(Driver $driver)
	{
		$this->equals('driver', $driver->id);
	}
}


class CarChecker extends LazyDataMapper\Checker
{

	protected function checkUpdate(LazyDataMapper\IEntity $icebox)
	{
		$this->checkRequired(['name']);
		$this->addCheck('integrity');
	}


	protected function checkCreate(LazyDataMapper\IEntity $icebox)
	{
		$this->addCheck('integrity');

		if ($icebox->price < 2000) {
			$this->addError('Too cheap car!', 'price');
		}
	}


	protected function checkIntegrity(LazyDataMapper\IEntity $icebox)
	{
		if (strlen($icebox->brand) > 20) {
			$this->addError('Brand is too long!', 'brand');
		}
	}
}


class CarParamMap extends LazyDataMapper\ParamMap
{

	protected $map = ['brand', 'name', 'engine', 'price', 'driver', 'chassis_id', 'repairs', ];
}


class CarMapper extends defaultMapper
{

	public static $calledGetById = 0;
	public static $calledGetByRestrictions = 0;

	/** @var LazyDataMapper\Suggestor */
	public static $lastSuggestor;

	/** @var LazyDataMapper\DataHolder */
	public static $lastHolder;

	public static $data;
	public static $default = [
		'brand' => '', 'name' => '', 'engine' => '0|0', 'price' => '0',
		'driver' => NULL, 'chassis_id' => '0', 'repairs' => '0',
	];

	public static $staticData = [
		1 => [
			'brand' => 'BMW', 'name' => 'Z4', 'engine' => '6|2979', 'price' => '16250',
			'driver' => '2', 'chassis_id' => '0', 'repairs' => '2',
		],
		2 => [
			'brand' => 'Lamborghini', 'name' => 'Gallardo', 'engine' => '10|5200', 'price' => '184000',
			'driver' => '3', 'chassis_id' => '1', 'repairs' => '1',
		],
		3 => [
			'brand' => 'Ford', 'name' => 'Mustang', 'engine' => '6|2838', 'price' => '19400',
			'driver' => '2', 'chassis_id' => '1', 'repairs' => '9',
		],
		4 => [
			'brand' => 'Lamborghini', 'name' => 'Diablo', 'engine' => '12|5760', 'price' => '211100',
			'driver' => '1', 'chassis_id' => '1', 'repairs' => '0',
		],
		5 => [
			'brand' => 'Toyota', 'name' => 'Celica', 'engine' => '4|1998', 'price' => '12740',
			'driver' => '5', 'chassis_id' => '2', 'repairs' => '12',
		],
		6 => [
			'brand' => 'Audi', 'name' => 'R8', 'engine' => '8|5320', 'price' => '19990',
			'driver' => '2', 'chassis_id' => '0', 'repairs' => '4',
		],
		7 => [
			'brand' => 'Skoda', 'name' => 'Yeti', 'engine' => '4|2000', 'price' => '10500',
			'driver' => '5', 'chassis_id' => '3', 'repairs' => '0',
		],
	];
}
