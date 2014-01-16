<?php

namespace Shelter;

abstract class Entity extends BaseEntity
{

	/** @var int */
	protected $id;

	/** @var string */
	private $identifier;

	/** @var IAccessor */
	protected $accessor;


	/**
	 * @param int $id
	 * @param array $params
	 * @param string $identifier
	 * @param IOperand $parent
	 * @param IAccessor $accessor
	 */
	public function __construct($id, array $params, $identifier, IOperand $parent = NULL, IAccessor $accessor){

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


	public function __get($paramName)
	{
		if ('id' === $paramName) {
			return $this->id;
		}
		return parent::__get($paramName);
	}


	protected function hasLazy($paramName)
	{
		return $this->mapper->hasParam($paramName);
	}


	protected function lazyLoad($paramName)
	{
		return $this->accessor->getParam($this, $paramName);
	}


	protected function translateParamName($param)
	{
		// infinite loop protection
		static $last;
		if ($last === $param) {
			return $param;
		}
		$last = $param;

		if (!$this->mapper->hasParam($param) && !$this->hasWrapper($param)) {
			return $this->translateParamNameUnderscore($param);
		}
		return $param;
	}


	public function save()
	{
		if ($this->isChanged() && $this->mapper->save($this, $this->getChanges())) {
			$this->bakeChanges();
		}
	}
}
