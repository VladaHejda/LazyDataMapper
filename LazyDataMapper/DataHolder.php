<?php

namespace LazyDataMapper;

/**
 * Based on Suggestor gains data from Mapper and gives data to Mapper's method save() and create().
 * @todo rename "params" to "data"
 */
class DataHolder implements \Iterator
{

	/** @var array */
	protected $params = array();

	/** @var array */
	protected $children = array();

	/** @var Suggestor */
	protected $suggestor;


	/**
	 * @param Suggestor $suggestor
	 * @throws Exception
	 * @todo co když $ids bude prázdný array (resp. mapper zjistí že žádní potomci nejsou)
	 */
	public function __construct(Suggestor $suggestor)
	{
		$this->suggestor = $suggestor;
	}


	private function isFlat(array $data)
	{
		$data = reset($data);
		if (!is_array($data)) {
			throw new Exception('You must set data via multi-dimensional array.');
		}
		$data = reset($data);
		return !is_array($data);
	}

	private function checkRecursively(array $zigzag, array $data)
	{
		$fork = FALSE;
		while (count($zigzag)) {
			$isContainer = array_shift($zigzag);
			// single entities on top does not fork the tree
			if (!$fork && !$isContainer) {
				continue;
			}
			$fork = TRUE;

			///// TODO ČEKOVAT
		}
	}

	private function containsContainer(array $zigzag)
	{
		return FALSE !== array_search(TRUE, $zigzag);
	}


	/**
	 * @param array|array[] $data array for one; array of arrays for container, indexed by id
	 * @return self
	 * @throws Exception on not suggested/unknown parameter
	 * @throws Exception on unknown id
	 */
	public function setParams(array $data)
	{
		$zigzag = $this->suggestor->getHierarchy()->getZigzag();

		$suggestions = array_fill_keys($this->suggestor->getSuggestions(), TRUE);

		// data for single entity
		if (!$this->containsContainer($zigzag)) {
			$this->checkAgainstSuggestions(array_keys($data), $suggestions);
			$this->params = $data + $this->params;

		// flat data based on ids
		} elseif ($this->isFlat($data)) {
			// todo isFlat() does not check if all members are arrays
			foreach ($data as $id => $params) {
				$this->checkAgainstSuggestions(array_keys($params), $suggestions);
				if (!isset($this->params[$id])) {
					$this->params[$id] = array();
				}
				$this->params[$id] = $params + $this->params[$id];
			}

		// tree data
		} else {
			$this->checkRecursively($zigzag, $data);
		}

		return $this;




		if (NULL === $this->ids && $this->suggestor->isContainer()) {
			throw new Exception('This DataHolder is Container and you did not set ids yet. Use method setIds().');
		}

		$suggestions = array_fill_keys($this->suggestor->getSuggestions(), TRUE);

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
