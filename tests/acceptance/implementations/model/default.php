<?php

namespace LazyDataMapper\Tests;

use LazyDataMapper;

abstract class defaultMapper implements LazyDataMapper\IMapper
{

	/** Inherit these static vars to get per Mapper results: */

	/** calls counters */
	public static $calledGetById, $calledGetByRestrictions;

	/** @var LazyDataMapper\ISuggestor */
	public static $lastSuggestor;

	/** @var LazyDataMapper\IDataHolder */
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


	public function getById($id, LazyDataMapper\ISuggestor $suggestor)
	{
		// analytics
		++static::$calledGetById;
		static::$lastSuggestor = $suggestor;

		$holder = new LazyDataMapper\DataHolder($suggestor);
		$data = array_intersect_key(static::$data[$id], array_flip($suggestor->getParamNames()));
		$holder->setParams($data);
		return $holder;
	}


	public function getByIdsRange(array $ids, LazyDataMapper\ISuggestor $suggestor)
	{
		++static::$calledGetByRestrictions;
		static::$lastSuggestor = $suggestor;

		$suggestions = array_flip($suggestor->getParamNames());
		$holder = new LazyDataMapper\DataHolder($suggestor, $ids);
		foreach ($ids as $id) {
			$data = array_intersect_key(static::$data[$id], $suggestions);
			$holder->setParams([$id => $data]);
		}

		return $holder;
	}


	public function getIdsByRestrictions(LazyDataMapper\IRestrictor $restrictor)
	{
		$restrictions = $restrictor->getRestrictions();

		$ids = [];
		foreach (static::$data as $id => $data) {
			if ($restrictions($data)) {
				$ids[] = $id;
			}
		}
		return $ids;
	}


	public function save($id, LazyDataMapper\IDataHolder $holder)
	{
		static::$lastHolder = $holder;
		static::$data[$id] = array_merge(static::$data[$id], $holder->getParams());
	}


	public function create(LazyDataMapper\IDataHolder $holder)
	{
		static::$data[] = array_merge(static::$default, $holder->getParams());
		end(static::$data);
		return key(static::$data);
	}


	public function remove($id)
	{
		unset(static::$data[$id]);
	}


	public function removeByIdsRange(array $ids)
	{
		foreach ($ids as $id) {
			$this->remove($id);
		}
	}
}
