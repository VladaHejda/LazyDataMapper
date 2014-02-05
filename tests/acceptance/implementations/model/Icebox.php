<?php

namespace Shelter\Tests;

use Shelter;

class Icebox extends Shelter\Entity
{

	protected function getFood($food)
	{
		return explode('|', $food);
	}


	protected function getCapacity($capacity)
	{
		return (int) $capacity;
	}


	public function hasFreezer()
	{
		return (bool) $this->freezer;
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

	protected $map = array(
		'color', 'capacity', 'freezer', 'food',
	);
}


class IceboxMapper implements Shelter\IMapper
{

	public static $calledCounter = array(
		'getById' => 0,
	);

	/** @var Shelter\ISuggestor */
	public static $lastSuggestor;

	private $data = array(
		2 => array('color' => 'black', 'capacity' => '45', 'freezer' => '0', 'food' => 'beef steak|milk|egg',),
		4 => array('color' => 'white', 'capacity' => '20', 'freezer' => '1', 'food' => 'egg|butter',),
		5 => array('color' => 'silver', 'capacity' => '25', 'freezer' => '1', 'food' => '',),
	);


	public function exists($id)
	{
		return isset($this->data[$id]);
	}


	public function getById($id, Shelter\ISuggestor $suggestor)
	{
		++self::$calledCounter['getById'];
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
		$this->paramMaps = array(
			__NAMESPACE__.'\Icebox' => new IceboxParamMap,
		);
		$this->mappers = array(
			__NAMESPACE__.'\Icebox' => new IceboxMapper,
		);
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
