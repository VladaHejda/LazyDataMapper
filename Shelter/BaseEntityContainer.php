<?php

namespace Shelter;

abstract class BaseEntityContainer implements \ArrayAccess, \Iterator, \Countable
{

	/** @var array[] */
	private $data;

	/** @var int last read offset */
	private $currentOffset;

	/** @var int iterator position */
	private $position;

	/** @var Entity last read Entity*/
	private $currentEntity;


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
				throw new Exception(get_class($this) . ': One member of data nested array has different count of params.');
			}
		}

		$this->data = $data;
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
	 * @throws Exception
	 */
	public function __get($param)
	{
		if (method_exists($this, $m = 'get' . ucfirst($param))) {
			return $this->$m();
		}

		throw new Exception(get_class($this) . ": cannot read an undeclared property $param");
	}


	/**
	 * @param $var
	 * @return bool
	 */
	public function __isset($var)
	{
		return method_exists($this, "get$var){");
	}


	/**
	 * @param mixed
	 * @return mixed|Entity
	 */
	public function offsetGet($index)
	{
		return $this->getEntity($index);
	}


	/**
	 * @param mixed
	 * @return bool
	 */
	public function offsetExists($index)
	{
		return isset($this->data[$index]);
	}


	/**
	 * @throws \BadMethodCallException
	 */
	public function offsetSet($x, $y)
	{
		throw new \BadMethodCallException(__CLASS__ . " cannot be modified.");
	}


	/**
	 * @throws \BadMethodCallException
	 */
	public function offsetUnset($x)
	{
		throw new \BadMethodCallException(__CLASS__ . ": Entity cannot be removed.");
	}


	final public function rewind()
	{
		$this->position = 0;
	}


	final public function valid()
	{
		return isset($this->data[$this->position]);
	}


	final public function current()
	{
		return $this[$this->position];
	}


	final public function next()
	{
		++$this->position;
	}


	final public function key()
	{
		return $this->position;
	}


	final public function count()
	{
		return count($this->data);
	}


	/**
	 * Returns array of each entity parameter value (better than iterate all Entity instances)
	 * @param string
	 * @return array
	 */
	protected function getParams($paramName)
	{
		if (!count($this)) {
			return array();
		}
		if (isset($this->data[0][$paramName])) {
			$seeker = function ($entityData) use ($paramName) {
				return $entityData[$paramName];
			};
			return array_map($seeker, $this->data);
		}

		// cause integration of param
		$params = array();
		foreach ($this as $index => $entity) {
			$params[] = $this->data[$index][$paramName] = $entity->$paramName;
		}
		return $params;
	}


	/**
	 * @param int
	 * @return Entity
	 * @throws Exception
	 */
	private function getEntity($index)
	{
		if (!isset($this->data[$index])) {
			throw new Exception(get_class($this) . ": no Entity on index $index.");
		}

		if ($this->currentOffset !== $index) { // intentionally !==
			$this->currentEntity = $this->createEntity($index, $this->data[$index]);

			if (!$this->currentEntity instanceof Entity) {
				throw new Exception(get_class($this) . "::createEntity() must return instance of Entity.");
			}
			$this->currentOffset = $index;
		}

		return $this->currentEntity;
	}
}
