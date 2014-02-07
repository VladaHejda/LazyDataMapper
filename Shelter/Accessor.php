<?php

namespace Shelter;

/**
 * @todo make some methods final (this is pivotal class of model)
 */
class Accessor implements IAccessor
{

	/** @var ISuggestorCache */
	protected $cache;

	/** @var IEntityServiceAccessor */
	protected $serviceAccessor;

	/** @var array */
	private $loadedData = array();


	/**
	 * @param ISuggestorCache $cache
	 * @param IEntityServiceAccessor $serviceAccessor
	 */
	public function __construct(ISuggestorCache $cache, IEntityServiceAccessor $serviceAccessor)
	{
		$this->cache = $cache;
		$this->serviceAccessor = $serviceAccessor;
	}


	/**
	 * Do not call this method directly!
	 * @param array|string $entityClass
	 * @param int $id
	 * @param IOperand $parent
	 * @param string $sourceParam
	 * @return IEntity
	 * @throws Exception
	 */
	final public function getById($entityClass, $id, IOperand $parent = NULL, $sourceParam = NULL)
	{
		list($entityClass) = $this->extractEntityClasses($entityClass);

		if (($parent && NULL === $sourceParam) || (NULL !== $sourceParam && !$parent)) {
			throw new Exception('Both $parent and $sourceParam must be set or omitted.');
		}

		$identifier = $this->serviceAccessor->composeIdentifier($entityClass, FALSE, $parent ? $parent->getIdentifier() : NULL, $sourceParam);

		if ($parent && $loadedData = $this->getLoadedData($parent->getIdentifier(), $entityClass, $sourceParam)) {
			$data = $loadedData;

		} else {
			if (!$this->serviceAccessor->getMapper($entityClass)->exists($id)) {
				return NULL;
			}

			if ($suggestor = $this->cache->getCached($identifier, $entityClass)) {
				$dataHolder = $this->loadDataHolderByMapper($entityClass, $id, $suggestor);
				$this->saveDescendants($dataHolder);
				$data = $dataHolder->getParams();

			} else {
				if ($parent) {
					$this->cache->cacheDescendant($parent->getIdentifier(), $entityClass, $sourceParam);
				}
				$data = array();
			}
		}

		return $this->createEntity($entityClass, $id, $data, $identifier);
	}


	/**
	 * Do not call this method directly!
	 * @param array|string $entityClass
	 * @param IRestrictor $restrictor
	 * @param IOperand $parent
	 * @return IEntityContainer
	 */
	public function getByRestrictions($entityClass, IRestrictor $restrictor, IOperand $parent = NULL)
	{
		list($entityClass, $entityContainerClass) = $this->extractEntityClasses($entityClass);

		$identifier = $this->serviceAccessor->composeIdentifier($entityClass, TRUE, $parent ? $parent->getIdentifier() : NULL);
		$ids = $this->serviceAccessor->getMapper($entityClass)->getIdsByRestrictions($restrictor);

		if (!empty($ids)) {
			$suggestor = $this->cache->getCached($identifier, $entityClass);
		}

		if (empty($ids) || !$suggestor) {
			$data = array();

		} else {
			$dataHolder = $this->loadDataHolderByMapper($entityClass, $ids, $suggestor);
			$this->saveDescendants($dataHolder);
			$data = $dataHolder->getParams();
			$this->sortData($ids, $data);
		}

		return $this->createEntityContainer($entityContainerClass, $data, $identifier);
	}


	public function hasParam(IEntity $entity, $paramName)
	{
		$entityClass = get_class($entity);
		return $this->serviceAccessor->getParamMap($entityClass)->hasParam($paramName);
	}


	/**
	 * @param IEntity $entity
	 * @param string $paramName
	 * @return string
	 */
	public function getParam(IEntity $entity, $paramName)
	{
		$entityClass = get_class($entity);
		$suggestor = $this->cache->cacheParamName($entity->getIdentifier(), $paramName, $entityClass);
		$dataHolder = $this->loadDataHolderByMapper($entityClass, $entity->getId(), $suggestor);
		return array_shift($dataHolder->getParams());
	}


	/**
	 * @param array|string $entityClass
	 * @param array $data
	 * @param bool $check
	 * @return IEntity
	 */
	public function create($entityClass, array $data, $check = TRUE)
	{
		list($entityClass) = $this->extractEntityClasses($entityClass);

		$dataHolder = $this->createDataHolder($entityClass, array_keys($data));
		$dataHolder->setParams($data);
		if ($check) {
			$this->check($entityClass, NULL, $dataHolder);
		}

		$id = $this->serviceAccessor->getMapper($entityClass)->create($dataHolder);
		$identifier = $this->serviceAccessor->composeIdentifier($entityClass);

		if ($suggestorCached = $this->cache->getCached($identifier, $entityClass)) {
			$data += $this->loadDataHolderByMapper($entityClass, $id, $suggestorCached);
		}

		return $this->createEntity($entityClass, $id, $data, $identifier);
	}


	/**
	 * @param IEntity $entity
	 */
	public function save(IEntity $entity)
	{
		$entityClass = get_class($entity);
		$data = $entity->getChanges();
		$dataHolder = $this->createDataHolder($entityClass, array_keys($data));
		$dataHolder->setParams($data);
		$this->check($entityClass, $entity, $dataHolder);
		$this->serviceAccessor->getMapper($entityClass)->save($entity->getId(), $dataHolder);
	}


	/**
	 * @param array|string $entityClass
	 * @param int $id
	 */
	public function remove($entityClass, $id)
	{
		list($entityClass) = $this->extractEntityClasses($entityClass);
		$this->serviceAccessor->getMapper($entityClass)->remove($id);
	}


	/********************* factories *********************/


	/**
	 * @param string $entityClass
	 * @param int $id
	 * @param array $data
	 * @param IIdentifier $identifier
	 * @return IEntity
	 */
	protected function createEntity($entityClass, $id, array $data, IIdentifier $identifier)
	{
		return new $entityClass($id, $data, $identifier, $this);
	}


	/**
	 * @param string $containerClass
	 * @param array[] $data
	 * @param IIdentifier $identifier
	 * @return IEntityContainer
	 */
	protected function createEntityContainer($containerClass, array $data, IIdentifier $identifier)
	{
		return new $containerClass($data, $identifier, $this);
	}


	/**
	 * @param string $entityClass
	 * @param array $paramNames
	 * @return DataHolder
	 */
	protected function createDataHolder($entityClass, array $paramNames)
	{
		$suggestor = new Suggestor($this->serviceAccessor->getParamMap($entityClass), $this->cache, $paramNames);
		return new DataHolder($suggestor);
	}


	/********************* internal *********************/


	/**
	 * @param string $entityClass
	 * @param int|int[] $id or ids
	 * @param ISuggestor $suggestor
	 * @return IDataHolder
	 * @throws Exception
	 */
	private function loadDataHolderByMapper($entityClass, $id, ISuggestor $suggestor)
	{
		$m = is_array($id) ? 'getByIdsRange' : 'getById';
		// todo and what about to check if serviceAccessor returns Mapper/ParamMap??
		$mapper = $this->serviceAccessor->getMapper($entityClass);
		$dataHolder = $mapper->$m($id, $suggestor);

		if (!$dataHolder instanceof IDataHolder) {
			throw new Exception(get_class($mapper) . "::$m() must return IDataHolder instance.");
		}
		return $dataHolder;
	}


	private function getLoadedData(IIdentifier $parentIdentifier, $entityClass, $sourceParam)
	{
		$parentIdentifier = $parentIdentifier->composeIdentifier();

		if (isset($this->loadedData[$parentIdentifier][$entityClass])) {
			$loaded = $this->loadedData[$parentIdentifier][$entityClass];
			if (is_array($loaded)) {
				if (isset($loaded[$sourceParam])) {
					return $loaded[$sourceParam];
				}
			} else {
				return $loaded[$entityClass];
			}
		}
		return FALSE;
	}


	// todo cachovat i data aktuálního? pak by se to teda nejmenoval saveDescendants ale saveData. a bylo by to k něčemu?
	private function saveDescendants(IDataHolder $dataHolder)
	{
		/** @var IDataHolder $descendant */
		foreach ($dataHolder as $descendant) {
			if ($descendant->getSuggestor()->hasDescendants()) {
				$this->saveDescendants($descendant);
			}

			$identifier = $descendant->getSuggestor()->getIdentifier();
			$this->loadedData[$identifier->composeIdentifier()] = $descendant->getParams();
		}
	}


	private function sortData(array $ids, array $data)
	{
		$sorted = array();
		foreach ($ids as $id) {
			$sorted[$id] = isset($data[$id]) ? $data[$id] : array();
		}
		return $sorted;
	}


	private function check($entityClass, $entity, IDataHolder $dataHolder)
	{
		$checker = $this->serviceAccessor->getChecker($entityClass);
		if ($checker) {
			$checker->check($entity, $dataHolder);
		}
	}


	/**
	 * @param array|string $entityClass
	 * @return array
	 */
	private function extractEntityClasses($entityClass)
	{
		return is_array($entityClass) ? $entityClass : array($entityClass, $this->serviceAccessor->getEntityContainerClass($entityClass));
	}
}
