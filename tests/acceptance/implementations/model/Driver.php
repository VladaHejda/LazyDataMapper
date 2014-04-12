<?php

namespace LazyDataMapper\Tests;

use LazyDataMapper;

require_once __DIR__ . '/default.php';
require_once __DIR__ . '/serviceAccessor.php';

class Driver extends LazyDataMapper\Entity
{

	protected $private = ['famous_cars'];


	protected function getName()
	{
		return "$this->first_name $this->last_name";
	}


	protected function getCars()
	{
		$restrictor = new CarRestrictor;
		$restrictor->limitDriver($this);
		return $this->getChild('LazyDataMapper\Tests\Car', $restrictor);
	}


	protected function getFamousCars()
	{
		$cars = explode('|', $this->getBase('famous_cars'));
		return $this->getChild('LazyDataMapper\Tests\Car', $cars);
	}


	protected function getColleague()
	{
		return $this->getChild();
	}


	protected function getNextDriver()
	{
		return $this->getChild(self::SELF, $this->id + 1);
	}
}


class DriverFacade extends LazyDataMapper\Facade
{
}


class Drivers extends LazyDataMapper\EntityCollection
{
}


class DriverRestrictor extends LazyDataMapper\FilterRestrictor
{

	public function limitFamousCars(LazyDataMapper\IOperand $car, $deny = FALSE)
	{
		if ($car instanceof Cars) {
			$ids = [];
			foreach ($car as $one) {
				$ids[] = $one->id;
			}
			$car = implode('|',  $ids);
		} else {
			$car = $car->id;
		}
		$pattern = $deny ? $this->getNotMatch('famous_cars') : $this->getMatch('famous_cars');
		if (empty($pattern)) {
			$pattern = "/\b($car)\b/";
		} else {
			$pattern = str_replace(')', "|$car)", $pattern);
		}

		if ($deny) {
			$this->notMatch('famous_cars', $pattern);
		} else {
			$this->match('famous_cars', $pattern);
		}
	}
}


class DriverParamMap extends LazyDataMapper\ParamMap
{

	protected $map = [
		'personal' => ['first_name', 'last_name', 'colleague'],
		'score' => ['wins', 'accidents'],
		'extra' => ['famous_cars'],
	];
}


class DriverMapper extends defaultMapper
{

	public static $calledGetById = 0;
	public static $calledGetByRestrictions = 0;

	/** @var LazyDataMapper\Suggestor */
	public static $lastSuggestor;

	public static $data;

	public static $staticData = [
		1 => [
			'first_name' => 'George', 'last_name' => 'Pooh', 'colleague' => '3',
			'wins' => '17', 'accidents' => '2', 'famous_cars' => '4|5'
		],
		2 => [
			'first_name' => 'John', 'last_name' => 'Gilbert', 'colleague' => '0',
			'wins' => '9', 'accidents' => '0', 'famous_cars' => '1'
		],
		3 => [
			'first_name' => 'Mike', 'last_name' => 'Norbert', 'colleague' => '1',
			'wins' => '24', 'accidents' => '31', 'famous_cars' => '1|4|5|7'
		],
		4 => [
			'first_name' => 'Billy', 'last_name' => 'Dilan', 'colleague' => '5',
			'wins' => '2', 'accidents' => '7', 'famous_cars' => ''
		],
		5 => [
			'first_name' => 'Christine', 'last_name' => 'Dorian', 'colleague' => '4',
			'wins' => '15', 'accidents' => '6', 'famous_cars' => '5'
		],
	];


	public function getById($id, LazyDataMapper\Suggestor $suggestor, LazyDataMapper\DataHolder $holder = NULL)
	{
		$holder = parent::getById($id, $suggestor, $holder);

		if ($suggestor->car) {
			$paramNames = array_flip($suggestor->car->getSuggestions());
			$cars = [];
			foreach (CarMapper::$data as $carId => $car) {
				if ($car['driver'] == $id) {
					$cars[$carId] = array_intersect_key($car ,$paramNames);
				}
			}
			$holder->car->setIds(array_keys($cars))->setParams($cars);
		}

		return $holder;
	}
}
