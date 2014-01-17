<?php

namespace Shelter;

abstract class Container implements \ArrayAccess, \Iterator, \Countable
{

	/** @var array[] */
	private $data;

	/** @var bool */
	private $initialized = FALSE;

	/** @var int last read offset */
	private $currentOffset;

	/** @var int iterator position */
	private $position;

	/** @var Entity last read Entity*/
	private $currentEntity;


	public function x__construct(array $data, $identifier, IOperand $parent = NULL, IAccessor $accessor, $entityClass)
	{
	}


	/**
	 * @param array[] $data array of params of each Entity, indexed by id
	 * @throws Exception
	 */
	public function __construct(array $data)
	{
		foreach ($data as $params) {
			if (!is_array($params)) {
				throw new Exception(get_class($this) . ': data must be array of arrays. One member of array is not an array.');
			}

			if (!isset($count)) {
				$count = count($params);

			} elseif (count($params) != $count) {
				throw new Exception('Method ' . __CLASS__ .'::load() must return array of param arrays, one member of array has different count of params.');
			}
		}

		$this->data = $data;

		$this->initialized = TRUE;
	}



	/**
	 * Implement loading array of params from data source.
	 * @return array of params of each Entity
	 */
	protected function load(){

		return array();
	}



	/**
	 * Extend this method to modify created Entity.
	 * @param int $index
	 * @param array $params
	 * @return Entity
	 */
	abstract protected function createEntity($index, array $params);



	/**
	 * @param string
	 * @throws \Nette\MemberAccessException
	 */
	public function __get($var){

		$this->checkInitialized();

		if (method_exists($this, $m = 'get' . ucfirst($var))) return $this->$m();

		throw new \Nette\MemberAccessException("Cannot read an undeclared property '$var'");
	}



	/**
	 * @param $var
	 * @return bool
	 */
	public function __isset($var){

		$this->checkInitialized();

		return method_exists($this, "get$var){");
	}



	/**
	 * @param mixed
	 * @return mixed|Entity
	 */
	public function offsetGet($index){

		$this->checkInitialized();

		return $this->getEntity($index);
	}



	/**
	 * @param mixed
	 * @return bool
	 */
	public function offsetExists($index){

		$this->checkInitialized();

		return isset($this->data[$index]);
	}



	/**
	 * @throws \BadMethodCallException
	 */
	public function offsetSet($x, $y){

		throw new \BadMethodCallException("Entity\\Container cannot be changed.");
	}



	/**
	 * @throws \BadMethodCallException
	 */
	public function offsetUnset($x){

		throw new \BadMethodCallException("Entity cannot be removed.");
	}



	final public function rewind(){

		$this->checkInitialized();

		$this->position = 0;
	}



	final public function valid(){

		return isset($this->data[$this->position]);
	}



	final public function current(){

		return $this[$this->position];
	}



	final public function next(){

		++$this->position;
	}



	final public function key(){

		return $this->position;
	}



	final public function count(){

		return count($this->data);
	}



	/**
	 * Returns array of each entity parameter value (better than iterate all Entity instances)
	 * @param string
	 * @return array
	 */
	protected function getParams($paramName){

		$this->checkInitialized();

		if (!count($this)) return array();

		if (isset($this->data[0][$paramName])) return array_map(function ($entityData) use ($paramName){

			return $entityData[$paramName];
		}, $this->data);

		// cause integration of param
		$params = array();
		foreach ($this as $index => $entity)
			$params[] = $this->data[$index][$paramName] = $entity->$paramName;
		return $params;
	}



	/**
	 * @param int
	 * @return Entity
	 * @throws \Nette\MemberAccessException
	 */
	private function getEntity($index){

		if (!isset($this->data[$index]))
			throw new \Nette\MemberAccessException("Entity\\Container has no Entity on index $index.");

		if ($this->currentOffset !== $index){ // intentionally !==

			$this->currentEntity = $this->createEntity($index, $this->data[$index]);

			if (!$this->currentEntity instanceof Entity)
				throw new \Nette\MemberAccessException(get_class() . "::createEntity() must return instance of Nais\\Entity.");

			$this->currentOffset = $index;
		}

		return $this->currentEntity;
	}



	private function checkInitialized(){

		if (!$this->initialized)
			throw new \Nette\InvalidStateException(get_class($this) . ": Object is not initialized, call parent::__construct().");
	}
}
