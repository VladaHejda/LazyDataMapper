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
	protected $children = array();

	/** @var Suggestor */
	protected $suggestor;

	/** @var array */
	protected $ids;


	/**
	 * @param Suggestor $suggestor
	 * @param int[] $ids for collection holder
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
		if (!$this->suggestor->isCollection()) {
			throw new Exception('Ids can be set only for Collection DataHolder.');
		}

		if (NULL !== $this->ids) {
			throw new Exception('Ids have already been set.');
		}

		$this->ids = $ids;
		return $this;
	}


	/**
	 * @param array|array[] $params array for one; array of arrays for collection, indexed by id
	 * @return self
	 * @throws Exception on not suggested/unknown parameter
	 * @throws Exception on unknown id
	 */
	public function setParams(array $params)
	{
		if (NULL === $this->ids && $this->suggestor->isCollection()) {
			throw new Exception('This DataHolder is Collection and you did not set ids yet. Use method setIds().');
		}

		$suggestions = array_fill_keys($this->suggestor->getSuggestions(), TRUE);

		if ($this->suggestor->isCollection()) {
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
		if ($this->suggestor->isCollection()) {
			$collectionMap = array();
			foreach ($this->params as $id => $params) {
				$collectionMap[$id] = $this->fillMap($map, $params);
			}
			return $collectionMap;
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
		if ($this->suggestor->isCollection()) {
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
	public function getChild($sourceParam, array $ids = NULL)
	{
		if (array_key_exists($sourceParam, $this->children)) {
			return $this->children[$sourceParam];
		}

		$suggestor = $this->suggestor->getChild($sourceParam);
		if (!$suggestor) {
			return NULL;
		}

		$child = new self($suggestor, $ids);
		$this->children[$sourceParam] = $child;
		return $child;
	}


	/**
	 * @see getChild()
	 */
	public function __get($sourceParam)
	{
		return $this->getChild($sourceParam);
	}


	/**
	 * Says whether children was loaded, not whether they exist. For that @see Suggestor::hasChildren
	 * @return bool
	 */
	public function hasLoadedChildren()
	{
		return !empty($this->children);
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
		if (array_key_exists($key, $this->children)) {
			return $this->children[$key];
		}
		$suggestor = $this->suggestor->current();
		if (!$suggestor) {
			return FALSE;
		}

		return $this->children[$key] = new self($suggestor);
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
