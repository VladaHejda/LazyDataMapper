<?php

namespace Shelter\Tests;

use Shelter;

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
}


class Iceboxes extends Shelter\EntityContainer
{}


class IceboxRestrictor implements Shelter\IRestrictor
{

	public function getRestrictions()
	{
	}
}


class IceboxFacade extends Shelter\Facade
{}


class IceboxParamMap extends Shelter\ParamMap
{

	protected $map = ['color', 'capacity', 'freezer', 'food', 'repairs', ];
}


class IceboxMapper implements Shelter\IMapper
{

	public static $calledGetById = 0;

	/** @var Shelter\ISuggestor */
	public static $lastSuggestor;

	private $data = [
		2 => ['color' => 'black', 'capacity' => '45', 'freezer' => '0', 'food' => 'beef steak|milk|egg', 'repairs' => '2',],
		4 => ['color' => 'white', 'capacity' => '20', 'freezer' => '1', 'food' => 'egg|butter', 'repairs' => '0',],
		5 => ['color' => 'silver', 'capacity' => '25', 'freezer' => '1', 'food' => '', 'repairs' => '4',],
	];


	public function exists($id)
	{
		return isset($this->data[$id]);
	}


	public function getById($id, Shelter\ISuggestor $suggestor)
	{
		// analytics
		++self::$calledGetById;
		self::$lastSuggestor = $suggestor;

		$holder = new Shelter\DataHolder($suggestor);
		$data = array_intersect_key($this->data[$id] ,array_flip($suggestor->getParamNames()));
		$holder->setParams($data);
		return $holder;
	}


	public function getIdsByRestrictions(Shelter\IRestrictor $restrictor){}

	public function getByIdsRange(array $ids, Shelter\ISuggestor $suggestor){}

	public function save($id, Shelter\IDataHolder $holder){}

	public function create(Shelter\IDataHolder $holder){}

	public function remove($id){}
}


class IceboxServiceAccessor extends Shelter\EntityServiceAccessor
{

	private $paramMaps;

	private $mappers;


	public function __construct()
	{
		$this->paramMaps = [
			__NAMESPACE__.'\Icebox' => new IceboxParamMap,
		];
		$this->mappers = [
			__NAMESPACE__.'\Icebox' => new IceboxMapper,
		];
	}


	public function getParamMap($entityClass)
	{
		return $this->paramMaps[$entityClass];
	}


	public function getMapper($entityClass)
	{
		return $this->mappers[$entityClass];
	}
}
