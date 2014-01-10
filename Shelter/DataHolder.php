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
			foreach ($params as $id => $plainParams) {
				$this->checkAgainstSuggestions(array_keys($plainParams), $suggestions);
				if (!isset($this->params[$id])) {
					$this->params[$id] = array();
				}
				$this->params[$id] = $plainParams + $this->params[$id];
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
		foreach ($map as $paramName => & $value) {
			if (array_key_exists($paramName, $this->params)) {
				$value = $this->params[$paramName];
			} else {
				unset ($map[$paramName]);
			}
		}
		return $map;
	}


	/**
	 * @param string $type
	 * @return bool
	 */
	public function isDataOnType($type)
	{
	}


	/**
	 * @param string $entityClass
	 * @param string $sourceParam
	 * @return self
	 */
	public function getDescendant($entityClass, $sourceParam = NULL)
	{
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
	}


	public function valid()
	{
	}


	public function current()
	{
	}


	public function key()
	{
	}


	public function next()
	{
	}


	private function checkAgainstSuggestions(array $paramNames, array $suggestions)
	{
		foreach ($paramNames as $paramName) {
			if (!isset($suggestions[$paramName])) {
				throw new Exception("Parameter $paramName is unknown or is not suggested.");
			}
		}
	}
}
