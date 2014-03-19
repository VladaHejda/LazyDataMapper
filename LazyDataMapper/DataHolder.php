<?php

namespace LazyDataMapper;

/**
 * Based on Suggestor gains data from Mapper and gives data to Mapper's method save() and create().
 */
class DataHolder implements \Iterator
{

	/** @var array */
	protected $params = array();

	/** @var array */
	protected $descendants = array();

	/** @var Suggestor */
	protected $suggestor;

	/** @var array */
	protected $ids;


	/**
	 * @param Suggestor $suggestor
	 * @param int[] $ids for container holder
	 * @throws Exception
	 */
	public function __construct(Suggestor $suggestor, array $ids = NULL)
	{
		$this->suggestor = $suggestor;
		if ($suggestor->isContainer() && NULL === $ids) {
			throw new Exception('Missing second argument for Container Suggestor. Expected array of ids.');
		}
		$this->ids = $ids;
	}


	/**
	 * @param array|array[] $params array for one; array of arrays for container, indexed by id
	 * @return self provides fluent interface
	 * @throws Exception on not suggested/unknown parameter
	 * @throws Exception on unknown id
	 */
	public function setParams(array $params)
	{
		$suggestions = array_fill_keys($this->suggestor->getParamNames(), TRUE);
		if ($this->suggestor->isContainer()) {
			if ($diff = array_diff(array_keys($params), $this->ids)) {
				if (is_int(current($diff))){
					throw new Exception("Invalid ids: " . implode(', ', $diff) . ".");
				}
				throw new Exception("You must set parameters for each id via two-dimensional array.");
			}
			foreach ($params as $id => $theParams) {
				$this->checkAgainstSuggestions(array_keys($theParams), $suggestions);
				if (!isset($this->params[$id])) {
					$this->params[$id] = array();
				}
				$this->params[$id] = $theParams + $this->params[$id];
			}
		} else {
			$this->checkAgainstSuggestions(array_keys($params), $suggestions);
			$this->params = $params + $this->params;
		}

		return $this;
	}


	/**
	 * @param string $group
	 * @return array
	 */
	public function getParams($group = NULL)
	{
		if (NULL === $group) {
			return $this->params;
		}

		$map = $this->suggestor->getParamMap()->getMap($group);
		if ($this->suggestor->isContainer()) {
			$containerMap = array();
			foreach ($this->params as $id => $params) {
				$containerMap[$id] = $this->fillMap($map, $params);
			}
			return $containerMap;
		}
		return $this->fillMap($map, $this->params);
	}


	/**
	 * @param string $group
	 * @return bool
	 * @throws Exception on unknown group
	 */
	public function isDataInGroup($group)
	{
		$map = $this->suggestor->getParamMap()->getMap($group, FALSE);
		if ($this->suggestor->isContainer()) {
			foreach ($this->params as $params) {
				$isDataInGroup = (bool) array_intersect(array_keys($params), $map);
				if ($isDataInGroup) {
					return TRUE;
				}
			}
			return FALSE;
		}
		return (bool) array_intersect(array_keys($this->params), $map);
	}


	/**
	 * @param string $param
	 * @return mixed
	 * @throws Exception
	 */
	public function __get($param)
	{
		if ($this->suggestor->isContainer()) {
			throw new Exception("For container DataHolder use method getParams().");
		}

		if (array_key_exists($param, $this->params)) {
			return $this->params[$param];
		}

		if ($this->suggestor->getParamMap()->hasParam($param)) {
			return NULL;
		}

		throw new Exception("Parameter $param does not exist.");
	}


	/**
	 * @param string $entityClass
	 * @param string $sourceParam
	 * @param int[] $ids
	 * @return self|null
	 * @throws Exception
	 */
	public function getDescendant($entityClass, &$sourceParam = NULL, array $ids = NULL)
	{
		if (!$this->suggestor->hasDescendant($entityClass, $sourceParam)) {
			return NULL;
		}

		$key = $this->suggestor->getDescendantIdentifier($entityClass, $sourceParam)->getKey();
		if (isset($this->descendants[$key])) {
			return $this->descendants[$key];
		}

		$suggestor = $this->suggestor->getDescendant($entityClass, $sourceParam);
		if ($suggestor->isContainer() && NULL === $ids) {
			throw new Exception('Missing third argument for descendant Container Suggestor. Expected array of ids.');
		}
		$descendantHolder = new self($suggestor, $ids);
		$this->descendants[$key] = $descendantHolder;
		return $descendantHolder;
	}


	/**
	 * @return Suggestor
	 */
	public function getSuggestor()
	{
		return $this->suggestor;
	}


	public function rewind()
	{
		$this->suggestor->rewind();
	}


	public function valid()
	{
		return $this->suggestor->valid();
	}


	public function current()
	{
		$suggestor = $this->suggestor->current();
		$key = $suggestor->getIdentifier()->getKey();
		if (isset($this->descendants[$key])) {
			return $this->descendants[$key];
		}
		return $this->descendants[$key] = new self($suggestor);
	}


	public function key()
	{
		return $this->suggestor->key();
	}


	public function next()
	{
		$this->suggestor->next();
	}


	private function checkAgainstSuggestions(array $paramNames, array $suggestions)
	{
		foreach ($paramNames as $paramName) {
			if (!isset($suggestions[$paramName])) {
				throw new Exception("Parameter $paramName is unknown or is not suggested.");
			}
		}
	}


	private function fillMap(array $map, array $params)
	{
		foreach ($map as $paramName => & $value) {
			if (array_key_exists($paramName, $params)) {
				$value = $params[$paramName];
			} else {
				unset ($map[$paramName]);
			}
		}
		return $map;
	}
}
