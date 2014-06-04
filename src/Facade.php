<?php

namespace LazyDataMapper;

/**
 * Outer cover for getting operands (Entity or EntityCollection).
 * There are two ways of determining Entity / EntityCollection classname:
 * - override methods loadEntityClass() and loadEntityCollectionClass() in the child of this class.
 * - apply solution in IEntityServiceAccessor method getEntityClass() and getEntityCollectionClass().
 *   There is some default solution, see DOCs.
 */
abstract class Facade
{

	/** @var Accessor */
	protected $accessor;

	/** @var EntityServiceAccessor */
	protected $serviceAccessor;

	/** @var string */
	private $entityClass, $entityCollectionClass;


	/**
	 * @param Accessor $accessor
	 * @param IEntityServiceAccessor $serviceAccessor
	 * @throws Exception
	 */
	public function __construct(Accessor $accessor, IEntityServiceAccessor $serviceAccessor)
	{
		$this->accessor = $accessor;
		$this->serviceAccessor = $serviceAccessor;
	}


	/**
	 * @return string
	 */
	protected function loadEntityClass()
	{
		return $this->serviceAccessor->getEntityClass($this);
	}


	/**
	 * @return string
	 */
	protected function loadEntityCollectionClass()
	{
		return $this->serviceAccessor->getEntityCollectionClass($this->getEntityClass());
	}


	/**
	 * @param int $id
	 * @return IEntity
	 */
	public function getById($id)
	{
		return $this->accessor->getEntity($this->getEntityClass(), $id);
	}


	/**
	 * @param int[] $ids
	 * @return IEntityCollection
	 */
	public function getByIdsRange(array $ids)
	{
		return $this->accessor->getCollection(array($this->getEntityClass(), $this->getEntityCollectionClass()), $ids);
	}


	/**
	 * @param IRestrictor $restrictor
	 * @param int $maxCount
	 * @return IEntityCollection
	 */
	public function getByRestrictions(IRestrictor $restrictor, $maxCount = 100)
	{
		return $this->accessor->getCollection(
			array($this->getEntityClass(), $this->getEntityCollectionClass()),
			$restrictor, NULL, NULL, $maxCount
		);
	}


	/**
	 * @param IRestrictor $restrictor
	 * @return IEntity
	 */
	public function getOneByRestrictions(IRestrictor $restrictor)
	{
		return $this->accessor->getEntity($this->getEntityClass(), $restrictor);
	}


	/**
	 * @param int $maxCount
	 * @return IEntityCollection
	 */
	public function getAll($maxCount = 100)
	{
		return $this->accessor->getCollection(
			array($this->getEntityClass(), $this->getEntityCollectionClass()),
			Accessor::ALL, NULL, NULL, $maxCount
		);
	}


	/**
	 * @param int $id
	 */
	public function remove($id)
	{
		$this->accessor->remove($this->getEntityClass(), $id);
	}


	/**
	 * @param int[] $ids
	 */
	public function removeByIdsRange(array $ids)
	{
		$this->accessor->removeByRestrictions($this->getEntityClass(), $ids);
	}


	/**
	 * @param IRestrictor $restrictor
	 */
	public function removeByRestrictions(IRestrictor $restrictor)
	{
		$this->accessor->removeByRestrictions($this->getEntityClass(), $restrictor);
	}


	/**
	 * For creation create method create() in child and require mandatory parameters for new Entity.
	 * @param array $publicData
	 * @param array $privateData
	 * @param bool $throwFirst whether throw first IntegrityException from Checker
	 * @return IEntity
	 */
	protected function createEntity(array $publicData, array $privateData = array(), $throwFirst = TRUE)
	{
		return $this->accessor->create($this->getEntityClass(), $publicData, $privateData, $throwFirst);
	}


	/**
	 * @return string
	 */
	final protected function getEntityClass()
	{
		if ($this->entityClass === NULL) {
			$this->entityClass = $this->loadEntityClass();
		}
		return $this->entityClass;
	}


	/**
	 * @return string
	 */
	final protected function getEntityCollectionClass()
	{
		if ($this->entityCollectionClass === NULL) {
			$this->entityCollectionClass = $this->loadEntityCollectionClass();
		}
		return $this->entityCollectionClass;
	}
}
