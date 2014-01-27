<?php

namespace Shelter;

/**
 * Outer cover for getting operands (Entity or EntityContainer).
 * Primary name entity-dependent descendant of this class like <Entity>Facade. If you
 * want use another naming convention, override self::$entityClass
 * or override IEntityServiceAccessor::getEntityClass().
 */
abstract class Facade implements IFacade
{

	/** @var string */
	protected $entityClass;

	/** @var IEntityServiceAccessor */
	private $serviceAccessor;

	/** @var IAccessor */
	private $accessor;


	/**
	 * @param IEntityServiceAccessor $serviceAccessor
	 * @param IAccessor $accessor
	 */
	public function __construct(IEntityServiceAccessor $serviceAccessor, IAccessor $accessor)
	{
		$this->serviceAccessor = $serviceAccessor;
		$this->accessor = $accessor;
		if (NULL === $this->entityClass) {
			$this->entityClass = $serviceAccessor->getEntityClass($this);
		}
	}


	/**
	 * @param int $id
	 * @return IEntity
	 */
	public function getById($id)
	{
		return $this->accessor->getById($this->entityClass, $id);
	}


	/**
	 * @param IRestrictor $restrictor
	 * @return IEntityContainer
	 */
	public function getByRestrictions(IRestrictor $restrictor)
	{
		return $this->accessor->getByRestrictions($this->entityClass, $restrictor);
	}


	/**
	 * Recommended to override and apply mandatory arguments for new Entity.
	 * @param array $data
	 * @param bool $check
	 * @return IEntity
	 */
	public function create(array $data = array(), $check = TRUE)
	{
		return $this->accessor->create($this->entityClass, $data, $check);
	}


	/**
	 * @param int $id
	 */
	function remove($id)
	{
		$this->accessor->remove($this->entityClass, $id);
	}
}
