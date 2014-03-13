<?php

namespace LazyDataMapper\Tests;

use LazyDataMapper;

require_once __DIR__ . '/default.php';
require_once __DIR__ . '/serviceAccessor.php';

class Race extends LazyDataMapper\Entity
{

	protected function getCar()
	{
		return $this->getDescendant('LazyDataMapper\Tests\Car');
	}
}


class Races extends LazyDataMapper\EntityContainer
{
}


class RaceFacade extends LazyDataMapper\Facade
{
}


class RaceRestrictor extends LazyDataMapper\FilterRestrictor
{

	public function limitCar(Car $car)
	{
		$this->equals('car', $car->id);
	}
}


class RaceParamMap extends LazyDataMapper\ParamMap
{

	protected $map = ['car', 'country'];
}


class RaceMapper extends defaultMapper
{

	public static $calledGetById = 0;

	/** @var LazyDataMapper\ISuggestor */
	public static $lastSuggestor;

	public static $data;

	public static $staticData = [
		1 => ['car' => '3', 'country' => 'Montana'],
		2 => ['car' => '3', 'country' => 'Iowa'],
		3 => ['car' => '1', 'country' => 'Ontario'],
		4 => ['car' => '2', 'country' => 'Texas'],
		5 => ['car' => '7', 'country' => 'Oregon'],
	];


	public function getById($id, LazyDataMapper\ISuggestor $suggestor, LazyDataMapper\IDataHolder $holder = NULL)
	{
		$holder = parent::getById($id, $suggestor, $holder);

		if ($suggestor->hasDescendant('LazyDataMapper\Tests\Car')) {
			$descendant = $suggestor->getDescendant('LazyDataMapper\Tests\Car');
			$data = array_intersect_key(CarMapper::$data[static::$data[$id]['car']] ,array_flip($descendant->getParamNames()));
			$descendantHolder = $holder->getDescendant('LazyDataMapper\Tests\Car');
			$descendantHolder->setParams($data);

			if ($descendant->hasDescendant('LazyDataMapper\Tests\Driver')) {
				$descendant = $descendant->getDescendant('LazyDataMapper\Tests\Driver');
				$data = array_intersect_key(DriverMapper::$data[$data['driver']] ,array_flip($descendant->getParamNames()));
				$descendantHolder->getDescendant('LazyDataMapper\Tests\Driver')->setParams($data);
			}
		}

		return $holder;
	}
}
