<?php

namespace LazyDataMapper;

abstract class EntityContainer implements IEntityContainer
{

	// todo think about protected/private visibility

	/** @var IIdentifier */
	protected $identifier;

	/** @var IAccessor */
	protected $accessor;

	/** @var int[] */
	private $ids;

	/** @var array[] */
	private $data;

	/** @var string */
	private $entityClass;

	/** @var int last read offset */
	private $currentOffset;

	/** @var int iterator position */
	private $position;

	/** @var IEntity last read Entity*/
	private $currentEntity;


	/**
	 * @param array[] $data array of params of each Entity, indexed by id, order dependent
	 * @param IIdentifier $identifier
	 * @param IAccessor $accessor
	 * @param string $entityClass
	 * @throws Exception
	 */
	public function __construct(array $data, IIdentifier $identifier, IAccessor $accessor, $entityClass)
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

		$this->ids = array_keys($data);
		$this->data = array_values($data);

		$this->identifier = $identifier;
		$this->entityClass = $entityClass;
		$this->accessor = $accessor;
	}


	public function getIdentifier()
	{
		return $this->identifier;
	}


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
	 * @return IEntity
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
	 * Creates Entity.
	 * @param int $id
	 * @param array $params
	 * @return IEntity
	 */
	protected function createEntity($id, array $params)
	{
		$entityClass = $this->entityClass;
		return new $entityClass($id, $params, $this->identifier, $this->accessor);
	}


	/**
	 * Returns array of each entity parameter value (better than iterate all Entity instances).
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
	 * @return IEntity
	 * @throws Exception
	 */
	private function getEntity($index)
	{
		if (!isset($this->data[$index])) {
			throw new Exception(get_class($this) . ": no Entity on index $index.");
		}

		if ($this->currentOffset !== $index) { // intentionally !==
			$this->currentEntity = $this->createEntity($this->ids[$index], $this->data[$index]);

			if (!$this->currentEntity instanceof IEntity) {
				throw new Exception(get_class($this) . "::createEntity() must return instance of Entity.");
			}
			$this->currentOffset = $index;
		}

		return $this->currentEntity;
	}
}
