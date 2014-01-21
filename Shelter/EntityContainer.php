<?php

namespace Shelter;

abstract class EntityContainer extends BaseEntityContainer
{

	/** @var string */
	protected $identifier;

	/** @var IAccessor */
	protected $accessor;

	/** @var string */
	private $entityClass;


	public function __construct(array $data, $identifier, IAccessor $accessor, $entityClass)
	{
		parent::__construct($data);
		$this->identifier = $identifier;
		$this->entityClass = $entityClass;
		$this->accessor = $accessor;
	}


	public function getIdentifier()
	{
		return $this->identifier;
	}


	protected function createEntity($id, array $params)
	{
		$entityClass = $this->entityClass;
		return new $entityClass($id, $params, $this->identifier, $this->accessor);
	}
}
