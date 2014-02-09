<?php

namespace Shelter\Tests;

use Shelter;

require_once __DIR__ . '/default.php';
require_once __DIR__ . '/serviceAccessor.php';

class Icebox extends Shelter\Entity
{

	protected $privateParams = ['repairs'];


	protected function getFood($food)
	{
		if (empty($food)) {
			return [];
		}
		return explode('|', $food);
	}


	protected function getFreezer($freezer)
	{
		return (bool) $freezer;
	}


	public function hasFreezer()
	{
		return $this->freezer;
	}


	protected function getCapacity($capacity, $unit = 'l')
	{
		$capacity = (int) $capacity;
		switch ($unit) {
			case 'l':
				return $capacity;
			case 'ml':
				return $capacity *1000;
		}
	}


	protected function getDescription()
	{
		return ucfirst($this->color) . " icebox, $this->capacity l.";
	}


	protected function getRepaired()
	{
		return (bool) $this->getClear('repairs');
	}
}


class Iceboxes extends Shelter\EntityContainer
{

	protected function getCapacity()
	{
		$total = 0;
		foreach ($this->getParams('capacity') as $capacity) {
			$total += $capacity;
		}
		return $total;
	}
}


class IceboxFacade extends Shelter\Facade
{

	protected $entityClass = ['Shelter\Tests\Icebox', 'Shelter\Tests\Iceboxes'];
}


class IceboxParamMap extends Shelter\ParamMap
{

	protected $map = ['color', 'capacity', 'freezer', 'food', 'repairs', ];
}


class IceboxMapper extends defaultMapper
{

	public static $calledGetById = 0;
	public static $calledGetByRestrictions = 0;

	/** @var Shelter\ISuggestor */
	public static $lastSuggestor;

	public static $data = [
		2 => ['color' => 'black', 'capacity' => '45', 'freezer' => '0', 'food' => 'beef steak|milk|egg', 'repairs' => '2', ],
		4 => ['color' => 'white', 'capacity' => '20', 'freezer' => '1', 'food' => 'egg|butter', 'repairs' => '0', ],
		5 => ['color' => 'silver', 'capacity' => '25', 'freezer' => '1', 'food' => '', 'repairs' => '4', ],
		8 => ['color' => 'blue', 'capacity' => '10', 'freezer' => '0', 'food' => 'jam', 'repairs' => '1', ],
	];
}
