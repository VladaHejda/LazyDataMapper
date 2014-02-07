<?php

namespace Shelter\Tests;

use Shelter;

abstract class defaultMapper implements Shelter\IMapper
{

	/** inherit $calledGetById counter to get per Mapper results */
	public static $calledGetById = 0;

	/**
	 * inherit $lastSuggestor to get per Mapper results
	 * @var Shelter\ISuggestor
	 */
	public static $lastSuggestor;

	/** @var array */
	public static $data;


	public function exists($id)
	{
		return isset(static::$data[$id]);
	}


	public function getById($id, Shelter\ISuggestor $suggestor)
	{
		// analytics
		++static::$calledGetById;
		static::$lastSuggestor = $suggestor;

		$holder = new Shelter\DataHolder($suggestor);
		$data = array_intersect_key(static::$data[$id] ,array_flip($suggestor->getParamNames()));
		$holder->setParams($data);
		return $holder;
	}


	public function getIdsByRestrictions(Shelter\IRestrictor $restrictor){}

	public function getByIdsRange(array $ids, Shelter\ISuggestor $suggestor){}

	public function save($id, Shelter\IDataHolder $holder){}

	public function create(Shelter\IDataHolder $holder){}

	public function remove($id){}
}
