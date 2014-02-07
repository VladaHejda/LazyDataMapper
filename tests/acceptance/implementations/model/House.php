<?php

namespace Shelter\Tests;

use Shelter,
	Shelter\Tests\KitchenMapper;

require_once __DIR__ . '/default.php';
require_once __DIR__ . '/serviceAccessor.php';

class House extends Shelter\Entity
{

	protected function getKitchen()
	{
		return $this->getDescendant('Shelter\Tests\Kitchen', 'kitchen');
	}
}

class HouseFacade extends Shelter\Facade
{}


class HouseParamMap extends Shelter\ParamMap
{

	protected $map = [
		'location' => ['no', 'street', ],
		'room' => ['kitchen', ],
	];
}


class HouseMapper extends defaultMapper
{

	public static $calledGetById = 0;

	/** @var Shelter\ISuggestor */
	public static $lastSuggestor;

	public static $data = [
		3 => ['no' => 115, 'street' => "King's road", 'kitchen' => 1, ],
		4 => ['no' => 240, 'street' => 'Oak', 'kitchen' => 2, ],
	];


	public function getById($id, Shelter\ISuggestor $suggestor)
	{
		$holder = parent::getById($id, $suggestor);

		if ($suggestor->hasDescendant('Shelter\Tests\Kitchen')) {
			$descendant = $suggestor->getDescendant('Shelter\Tests\Kitchen');
			$data = array_intersect_key(KitchenMapper::$data[static::$data[$id]['kitchen']] ,array_flip($descendant->getParamNames()));
			$holder->getDescendant('Shelter\Tests\Kitchen')->setParams($data);
		}

		return $holder;
	}
}
