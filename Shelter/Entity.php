<?php

namespace Shelter;

abstract class Entity extends BaseEntity
{

	/** @var int */
	protected $id;

	/** @var string */
	protected $identifier;

	/** @var IAccessor */
	protected $accessor;


	/**
	 * @param int $id
	 * @param array $params
	 * @param string $identifier
	 * @param IAccessor $accessor
	 */
	public function __construct($id, array $params, $identifier, IAccessor $accessor)
	{
		$this->id = (int) $id;
		$this->identifier = $identifier;
		if ($accessor) $this->accessor = $accessor;

		parent::__construct($params);
	}


	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}


	/**
	 * @return string
	 */
	public function getIdentifier()
	{
		return $this->identifier;
	}


	/**
	 * @param string $paramName
	 * @return bool
	 */
	protected function hasLazy($paramName)
	{
		return $this->accessor->hasParam($this, $paramName);
	}


	/**
	 * @param string $paramName
	 * @return string
	 */
	protected function lazyLoad($paramName)
	{
		return $this->accessor->getParam($this, $paramName);
	}


	/**
	 * @return void
	 */
	public function save()
	{
		if ($this->isChanged()) {
			$this->accessor->save($this);
			$this->bakeChanges();
		}
	}


	/**
	 * @param string $entityClass
	 * @param string|IRestrictor $sourceParamOrRestrictor
	 * @param int|null $id id of Entity, when not accessible from source parameter
	 * @return IOperand
	 */
	protected function getDescendant($entityClass, $sourceParamOrRestrictor, $id = NULL)
	{
		if ($sourceParamOrRestrictor instanceof IRestrictor) {
			return $this->accessor->getByRestrictions($entityClass, $sourceParamOrRestrictor, $this);
		} else {
			if (NULL === $id) {
				$id = $this->getClear($sourceParamOrRestrictor);
			}
			return $this->accessor->getById($entityClass, $id, $this, $sourceParamOrRestrictor);
		}
	}


	/**
	 * @param string $paramName
	 * @return string
	 */
	protected function translateParamName($paramName)
	{
		// infinite loop protection
		static $last;
		if ($last === $paramName) {
			return $paramName;
		}
		$last = $paramName;

		if (!$this->accessor->hasParam($this, $paramName) && !$this->hasWrapper($paramName)) {
			return $this->translateParamNameUnderscore($paramName);
		}
		return $paramName;
	}
}
