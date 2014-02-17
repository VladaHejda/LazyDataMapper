<?php

namespace Shelter\Tests;

use Shelter;

abstract class defaultMapper implements Shelter\IMapper
{

	/** Inherit these static vars to get per Mapper results: */

	/** calls counters */
	public static $calledGetById, $calledGetByRestrictions;

	/** @var Shelter\ISuggestor */
	public static $lastSuggestor;

	/** @var Shelter\IDataHolder */
	public static $lastHolder;

	/** @var array */
	public static $data;

	/** @var array */
	public static $staticData = [];

	/** @var array */
	public static $default = [];


	public function __construct()
	{
		// reset modified data
		static::$data = static::$staticData;
	}


	public static function resetCounters()
	{
		static::$calledGetById = 0;
		static::$calledGetByRestrictions = 0;
	}


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
		$data = array_intersect_key(static::$data[$id], array_flip($suggestor->getParamNames()));
		$holder->setParams($data);
		return $holder;
	}


	public function getByIdsRange(array $ids, Shelter\ISuggestor $suggestor)
	{
		++static::$calledGetByRestrictions;
		static::$lastSuggestor = $suggestor;

		$suggestions = array_flip($suggestor->getParamNames());
		$holder = new Shelter\DataHolder($suggestor, $ids);
		foreach ($ids as $id) {
			$data = array_intersect_key(static::$data[$id], $suggestions);
			$holder->setParams([$id => $data]);
		}

		return $holder;
	}


	public function getIdsByRestrictions(Shelter\IRestrictor $restrictor){}


	public function save($id, Shelter\IDataHolder $holder)
	{
		static::$lastHolder = $holder;
		static::$data[$id] = array_merge(static::$data[$id], $holder->getParams());
	}


	public function create(Shelter\IDataHolder $holder)
	{
		static::$data[] = array_merge(static::$default, $holder->getParams());
		end(static::$data);
		return key(static::$data);
	}


	public function remove($id)
	{
		unset(static::$data[$id]);
	}
}
