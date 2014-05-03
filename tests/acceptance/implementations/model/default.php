<?php

namespace LazyDataMapper\Tests;

use LazyDataMapper;

abstract class defaultMapper implements LazyDataMapper\IMapper
{

	/** Inherit these static vars to get per Mapper results: */

	/** calls counters */
	public static $calledGetById, $calledGetByRestrictions;

	/** @var LazyDataMapper\Suggestor */
	public static $lastSuggestor;

	/** @var LazyDataMapper\DataHolder */
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


	public function getById($id, LazyDataMapper\Suggestor $suggestor, LazyDataMapper\DataHolder $holder = NULL)
	{
		// analytics
		++static::$calledGetById;
		static::$lastSuggestor = $suggestor;
		static::$lastHolder = $holder;

		$data = array_intersect_key(static::$data[$id], array_flip($suggestor->getSuggestions()));
		$holder->setData($data);
		return $holder;
	}


	public function getByIdsRange(array $ids, LazyDataMapper\Suggestor $suggestor, LazyDataMapper\DataHolder $holder = NULL)
	{
		++static::$calledGetByRestrictions;
		static::$lastSuggestor = $suggestor;
		static::$lastHolder = $holder;

		$suggestions = array_flip($suggestor->getSuggestions());
		foreach ($ids as $id) {
			$data = array_intersect_key(static::$data[$id], $suggestions);
			$holder->setData([$id => $data]);
		}

		return $holder;
	}


	public function getIdsByRestrictions(LazyDataMapper\IRestrictor $restrictor, $maxCount = NULL)
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


	public function save($id, LazyDataMapper\DataHolder $holder)
	{
		static::$lastHolder = $holder;
		static::$data[$id] = array_merge(static::$data[$id], $holder->getData());
	}


	public function create(LazyDataMapper\DataHolder $holder)
	{
		static::$data[] = array_merge(static::$default, $holder->getData());
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
