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


class RaceFacade extends LazyDataMapper\Facade
{
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
		1 => ['car' => '4', 'country' => 'Montana'],
		2 => ['car' => '1', 'country' => 'iowa'],
		3 => ['car' => '2', 'country' => 'Ontario'],
		4 => ['car' => '2', 'country' => 'Texas'],
		5 => ['car' => '7', 'country' => 'Oregon'],
	];


	public function getById($id, LazyDataMapper\ISuggestor $suggestor, LazyDataMapper\IDataHolder $holder = NULL)
	{
		$holder = parent::getById($id, $suggestor, $holder);

		if ($suggestor->hasDescendant('LazyDataMapper\Tests\Car')) {
			$descendant = $suggestor->getDescendant('LazyDataMapper\Tests\Car');
			$data = array_intersect_key(CarMapper::$data[static::$data[$id]['car']] ,array_flip($descendant->getParamNames()));
			$holder->getDescendant('LazyDataMapper\Tests\Car')->setParams($data);
		}

		return $holder;
	}
}
