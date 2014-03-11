<?php

namespace LazyDataMapper\Tests;

use LazyDataMapper,
	LazyDataMapper\Tests\KitchenMapper;

require_once __DIR__ . '/default.php';
require_once __DIR__ . '/serviceAccessor.php';

class House extends LazyDataMapper\Entity
{

	protected function getKitchen()
	{
		return $this->getDescendant('LazyDataMapper\Tests\Kitchen');
	}
}

class HouseFacade extends LazyDataMapper\Facade
{}


class HouseParamMap extends LazyDataMapper\ParamMap
{

	protected $map = [
		'location' => ['no', 'street', ],
		'room' => ['kitchen', ],
	];
}


class HouseMapper extends defaultMapper
{

	public static $calledGetById = 0;

	/** @var LazyDataMapper\ISuggestor */
	public static $lastSuggestor;

	public static $data;

	public static $staticData = [
		3 => ['no' => 115, 'street' => "King's road", 'kitchen' => 1, ],
		4 => ['no' => 240, 'street' => 'Oak', 'kitchen' => 2, ],
	];


	public function getById($id, LazyDataMapper\ISuggestor $suggestor, LazyDataMapper\IDataHolder $holder = NULL)
	{
		$holder = parent::getById($id, $suggestor, $holder);

		if ($suggestor->hasDescendant('LazyDataMapper\Tests\Kitchen')) {
			$descendant = $suggestor->getDescendant('LazyDataMapper\Tests\Kitchen');
			$data = array_intersect_key(KitchenMapper::$data[static::$data[$id]['kitchen']] ,array_flip($descendant->getParamNames()));
			$holder->getDescendant('LazyDataMapper\Tests\Kitchen')->setParams($data);
		}

		return $holder;
	}
}
