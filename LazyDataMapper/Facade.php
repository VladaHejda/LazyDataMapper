<?php

namespace LazyDataMapper;

/**
 * Outer cover for getting operands (Entity or EntityCollection).
 * There are two ways of determining Entity / EntityCollection classname:
 * - override property $entityClass in the child of this class due to the pattern:
 *   either string <EntityClassname>
 *   or array [<EntityClassname>, <EntityCollectionClassname>]
 * - apply solution in IEntityServiceAccessor method getEntityClass() and getEntityCollectionClass().
 *   There is some default solution.
 */
abstract class Facade
{

	/** @var array|string */
	protected $entityClass;

	/** @var Accessor */
	private $accessor;


	/**
	 * @param Accessor $accessor
	 * @param IEntityServiceAccessor $serviceAccessor
	 * @throws Exception
	 */
	public function __construct(Accessor $accessor, IEntityServiceAccessor $serviceAccessor = NULL)
	{
		$this->accessor = $accessor;

		$this->entityClass = (array) $this->entityClass;

		$predefinedCount = count($this->entityClass);
		if (!$predefinedCount || ($serviceAccessor && $predefinedCount == 1)) {
			if (!$serviceAccessor) {
				$class = get_class($this);
				throw new Exception($class . ": inject IEntityServiceAccessor or fill the $class::\$entityClass property.");
			}

			if (!$predefinedCount) {
				$entityClass = $serviceAccessor->getEntityClass($this);
				// todo check this globally - even if classnames set by Facade descendant
				if (!is_string($entityClass) || empty($entityClass)) {
					throw new Exception(get_class($this) . ": IEntityServiceAccessor::getEntityClass() does not return classname.");
				}
				$this->entityClass[] = $entityClass;
			}

			$entityCollectionClass = $serviceAccessor->getEntityCollectionClass(reset($this->entityClass));
			if (!is_string($entityCollectionClass) || empty($entityCollectionClass)) {
				throw new Exception(get_class($this) . ": IEntityServiceAccessor::getEntityCollectionClass() does not return classname.");
			}
			$this->entityClass[] = $entityCollectionClass;
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
	 * @param int[] $ids
	 * @return IEntityCollection
	 */
	public function getByIdsRange(array $ids)
	{
		return $this->accessor->getByRestrictions($this->entityClass, $ids);
	}


	/**
	 * @param IRestrictor $restrictor
	 * @param int $maxCount
	 * @return IEntityCollection
	 */
	public function getByRestrictions(IRestrictor $restrictor, $maxCount = 100)
	{
		return $this->accessor->getByRestrictions($this->entityClass, $restrictor, NULL, NULL, $maxCount);
	}


	/**
	 * @param int $id
	 */
	public function remove($id)
	{
		$this->accessor->remove($this->entityClass, $id);
	}


	/**
	 * @param int[] $ids
	 */
	public function removeByIdsRange(array $ids)
	{
		$this->accessor->removeByRestrictions($this->entityClass, $ids);
	}


	/**
	 * @param IRestrictor $restrictor
	 */
	public function removeByRestrictions(IRestrictor $restrictor)
	{
		$this->accessor->removeByRestrictions($this->entityClass, $restrictor);
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
		return $this->accessor->create($this->entityClass, $publicData, $privateData, $throwFirst);
	}
}
