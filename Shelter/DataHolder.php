<?php

namespace Shelter;

class DataHolder implements IDataHolder
{

	/** @var array */
	protected $params = array();

	/** @var ISuggestor */
	protected $suggestor;

	/** @var array */
	protected $ids;

	/** @var bool */
	protected $isContainer;


	/**
	 * @param ISuggestor $suggestor
	 * @param array $ids for container holder
	 */
	public function __construct(ISuggestor $suggestor, array $ids = NULL)
	{
		$this->suggestor = $suggestor;
		$this->ids = $ids;
		$this->isContainer = NULL !== $ids;
	}


	/**
	 * @param array|array[] $params array for one; array of arrays for container, indexed by id
	 * @return void
	 * @throws Exception on not suggested/unknown parameter
	 * @throws Exception on unknown id
	 */
	public function setParams(array $params)
	{
		$suggestions = array_fill_keys($this->suggestor->getParamNames(), TRUE);
		if ($this->isContainer) {
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
	}


	/**
	 * @param string $type
	 * @return array
	 */
	public function getParams($type = NULL)
	{
		if (NULL === $type) {
			return $this->params;
		}

		$map = $this->suggestor->getParamMap()->getMap($type);
		if ($this->isContainer) {
			$containerMap = array();
			foreach ($this->params as $id => $params) {
				$containerMap[$id] = $this->fillMap($map, $params);
			}
			return $containerMap;
		}
		return $this->fillMap($map, $this->params);
	}


	/**
	 * @param string $type
	 * @return bool
	 */
	public function isDataOnType($type)
	{
		$map = $this->suggestor->getParamMap()->getMap($type, FALSE);
		if ($this->isContainer) {
			foreach ($this->params as $params) {
				$isDataOnType = (bool) array_intersect(array_keys($params), $map);
				if ($isDataOnType) {
					return TRUE;
				}
			}
			return FALSE;
		}
		return (bool) array_intersect(array_keys($this->params), $map);
	}


	/**
	 * @param string $entityClass
	 * @param string $sourceParam
	 * @return self
	 */
	public function getDescendant($entityClass, &$sourceParam = NULL)
	{
		$suggestor = $this->suggestor->getDescendant($entityClass, $sourceParam);
		return $suggestor ? new self($suggestor) : NULL;
	}


	/**
	 * @return ISuggestor
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
		return new self($this->suggestor->current());
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
