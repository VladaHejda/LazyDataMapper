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
	 * @todo co když $ids bude prázdný array (resp. mapper zjistí že žádní potomci nejsou)
	 */
	public function __construct(Suggestor $suggestor, array $ids = NULL)
	{
		$this->suggestor = $suggestor;

		if (NULL !== $ids) {
			$this->setIds($ids);
		}
	}


	/**
	 * @param array $ids
	 * @return self
	 * @throws Exception
	 */
	public function setIds(array $ids)
	{
		if (!$this->suggestor->isContainer()) {
			throw new Exception('Ids can be set only for Container DataHolder.');
		}

		if (NULL !== $this->ids) {
			throw new Exception('Ids have already been set.');
		}

		$this->ids = $ids;
		return $this;
	}


	/**
	 * @param array|array[] $params array for one; array of arrays for container, indexed by id
	 * @return self
	 * @throws Exception on not suggested/unknown parameter
	 * @throws Exception on unknown id
	 */
	public function setParams(array $params)
	{
		if (NULL === $this->ids && $this->suggestor->isContainer()) {
			throw new Exception('This DataHolder is Container and you did not set ids yet. Use method setIds().');
		}

		$suggestions = array_fill_keys($this->suggestor->getParamNames(), TRUE);

		if ($this->suggestor->isContainer()) {
			if ($diff = array_diff(array_keys($params), $this->ids)) {
				if (is_int(current($diff))){
					throw new Exception('Invalid ids: ' . implode(', ', $diff) . '.');
				}
				throw new Exception('You must set parameters for each id via two-dimensional array.');
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
	 * @param string $sourceParam
	 * @param int[] $ids
	 * @return self|null
	 * @throws Exception
	 */
	public function getDescendant($sourceParam, array $ids = NULL)
	{
		if (array_key_exists($sourceParam, $this->descendants)) {
			return $this->descendants[$sourceParam];
		}

		$suggestor = $this->suggestor->getDescendant($sourceParam);
		if (!$suggestor) {
			return NULL;
		}

		$descendant = new self($suggestor, $ids);
		$this->descendants[$sourceParam] = $descendant;
		return $descendant;
	}


	/**
	 * @see getDescendant()
	 */
	public function __get($sourceParam)
	{
		return $this->getDescendant($sourceParam);
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
		$key = $this->suggestor->key();
		if (array_key_exists($key, $this->descendants)) {
			return $this->descendants[$key];
		}
		$suggestor = $this->suggestor->current();
		if (!$suggestor) {
			return FALSE;
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
