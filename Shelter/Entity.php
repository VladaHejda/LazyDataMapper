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
	 * @param IOperand $parent
	 * @param IAccessor $accessor
	 */
	public function __construct($id, array $params, $identifier, IOperand $parent = NULL, IAccessor $accessor)
	{
		$this->id = (int) $id;
		$this->identifier = $identifier;
		if ($accessor) $this->accessor = $accessor;

		parent::__construct($params);
	}


	public function getId()
	{
		return $this->id;
	}


	public function getIdentifier()
	{
		return $this->identifier;
	}


	protected function hasLazy($paramName)
	{
		return $this->accessor->hasParam($this, $paramName);
	}


	protected function lazyLoad($paramName)
	{
		return $this->accessor->getParam($this, $paramName);
	}


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


	public function save()
	{
		if ($this->isChanged()) {
			$this->accessor->save($this);
			$this->bakeChanges();
		}
	}
}
