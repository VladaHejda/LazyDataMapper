<?php

namespace Shelter\Tests;

use Shelter;

abstract class defaultMapper implements Shelter\IMapper
{

	public static $calledGetById = 0;

	/** @var Shelter\ISuggestor */
	public static $lastSuggestor;

	/** @var array */
	protected $data;


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

abstract class defaultServiceAccessor extends Shelter\EntityServiceAccessor
{

	protected $paramMaps;

	protected $mappers;


	/**
	 * Set paramMaps and mappers.
	 */
	abstract public function __construct();


	public function getParamMap($entityClass)
	{
		return $this->paramMaps[$entityClass];
	}


	public function getMapper($entityClass)
	{
		return $this->mappers[$entityClass];
	}
}
